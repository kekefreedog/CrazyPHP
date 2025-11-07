/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2025 Kévin Zarshenas
 */

/**
 * Dependances
 */
import localforage from 'localforage';
import Strings from './Strings';

/**
 * Page Cache Manager
 *
 * Methods for manage page cache using
 * Service Worker CacheStorage or LocalForage
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2025 Kévin Zarshenas
 */
export default class PageCacheManager {

    /** Private parameters
     ******************************************************
     */

    /** @param prefix */
    private _prefix = 'page-cache-';

    /** @param cacheName */
    private _cacheName = 'MyCrazyApp-PageCache';

    /** @param mode */
    private _mode:'cache'|'localforage' = 'cache';

    /**
     * Constructor
     * 
     * @param mode
     */
    public constructor(mode:'cache'|'localforage' = 'cache') {

        // Set mode
        this._mode = mode;

        // Set localforage config
        localforage.config({
            name: 'MyCrazyApp',
            storeName: 'page_cache',
            description: 'Stores Crazy App Pages',
        });

        // Check cache support
        if(this._mode === 'cache' && !('caches' in window)){

            // Warn
            console.warn('⚠️ CacheStorage not supported, switching to localForage.');

            // Fallback
            this._mode = 'localforage';

        }

    }

    /** Public async parameters
     ******************************************************
     */

    /**
     * Has
     * 
     * Has Page
     * 
     * @param page 
     * @param currentHash 
     * @returns 
     */
    public has = async (page:string, currentHash:string):Promise<boolean> => {

        // Init result
        let result:boolean = false;

        // Check inputs
        if(page && currentHash){

            // If localforage mode
            if(this._mode === 'localforage'){

                // Get item
                const data = await localforage.getItem<CachedPage>(this._key(page) as string);

                // Set result
                result = !!data && data.hash === currentHash;

            }else
            // If cache mode
            {

                // Open cache
                const cache = await caches.open(this._cacheName);

                // Set key
                const key = this._key(page, currentHash) as Request;

                // Match cache
                const response = await cache.match(key);

                // Set result
                result = !!response;

            }

        }

        // Return result
        return result;

    }

    /**
     * Get
     * 
     * Get Page
     * 
     * @param page 
     * @returns 
     */
    public get = async (page:string):Promise<string|null> => {

        // Init result
        let result:string|null = null;

        // Check page
        if(page){

            // If localforage mode
            if(this._mode === 'localforage'){

                // Get data
                const data = await localforage.getItem<CachedPage>(this._key(page) as string);

                // Set result
                result = data ? data.content : null;

            }else
            // If cache mode
            {

                // Open cache
                const cache = await caches.open(this._cacheName);

                // Get keys
                const keys = await cache.keys();

                // Find match
                const match = keys.find(req => req.url.includes(`${this._prefix}${page}`));

                // If match
                if(match){

                    // Match response
                    const response = await cache.match(match);

                    // Set result
                    result = response ? await response.text() : null;

                }

            }

        }

        // Return result
        return result;

    }

    /**
     * Set
     * 
     * Set Page
     * 
     * @param page 
     * @param hash 
     * @param content 
     * @returns 
     */
    public set = async (page:string, hash:string, content:string):Promise<void> => {

        // Check inputs
        if(page && hash && content){

            // If localforage mode
            if(this._mode === 'localforage'){

                // Set data
                const data:CachedPage = { hash, content };

                // Set item
                await localforage.setItem(this._key(page) as string, data);

            }else
            // If cache mode
            {

                // Open cache
                const cache = await caches.open(this._cacheName);

                // Set key
                const key = this._key(page, hash) as Request;

                // Create response
                const response = new Response(content, {
                    headers: { 'Content-Type': 'text/html; charset=utf-8' },
                });

                // Put in cache
                await cache.put(key, response);

            }

        }

    }

    /**
     * Delete
     * 
     * Remove Page
     * 
     * @param page 
     * @returns 
     */
    public delete = async (page:string):Promise<void> => {

        // Check page
        if(page){

            // If localforage mode
            if(this._mode === 'localforage'){

                // Remove item
                await localforage.removeItem(this._key(page) as string);

            }else
            // If cache mode
            {

                // Open cache
                const cache = await caches.open(this._cacheName);

                // Get keys
                const keys = await cache.keys();

                // Loop keys
                for(const req of keys){

                    // If match
                    if(req.url.includes(`${this._prefix}${page}`)){

                        // Delete request
                        await cache.delete(req);

                    }

                }

            }

        }

    }

    /**
     * Delete All
     * 
     * @returns 
     */
    public deleteAll = async ():Promise<void> => {

        // If localforage mode
        if(this._mode === 'localforage'){

            // Clear
            await localforage.clear();

        }else
        // If cache mode
        {

            // Get cache keys
            const keys = await caches.keys();

            // Loop keys
            for(const key of keys){

                // If starts with name
                if(key.startsWith(this._cacheName)){

                    // Delete cache
                    await caches.delete(key);

                }

            }

        }

    }

    /**
     * Fetch
     * 
     * Fetch Page
     * 
     * @param pageUrl 
     * @returns 
     */
    public fetch = async (pageUrl:string):Promise<string|null> => {

        // Init result
        let result:string|null = null;

        // Check input
        if(pageUrl){

            // Set filename
            const filename = pageUrl.split('/').pop() || pageUrl;

            // Set base name
            const baseName = filename.split('.')[0];

            // Set hash
            const hash = Strings.extractHash(pageUrl) || 'nohash';

            // If localforage mode
            if(this._mode === 'localforage'){

                // Get cached
                const cached = await localforage.getItem<CachedPage>(this._key(baseName) as string);

                // If valid
                if(cached && cached.hash === hash){

                    // Set result
                    result = cached.content;

                }else{

                    // Fetch new
                    const res = await fetch(pageUrl);

                    // Set text
                    const text = await res.text();

                    // Store
                    await this.set(baseName, hash, text);

                    // Set result
                    result = text;

                }

            }else
            // If cache mode
            {

                // Open cache
                const cache = await caches.open(this._cacheName);

                // Set key
                const key = this._key(baseName, hash) as Request;

                // Try cached
                const cachedResponse = await cache.match(key);

                // If found
                if(cachedResponse){

                    // Log
                    console.log(`✅ Using cached ${baseName}@${hash}`);

                    // Set result
                    result = await cachedResponse.text();

                }else{

                    // Fetch
                    console.log(`🌐 Fetching fresh ${baseName}@${hash}`);

                    // Get response
                    const networkResponse = await fetch(pageUrl);

                    // If ok
                    if(networkResponse.ok){

                        // Put in cache
                        await cache.put(key, networkResponse.clone());

                        // Set result
                        result = await networkResponse.text();

                    }else{

                        // Warn
                        console.warn(`⚠️ Failed to fetch ${pageUrl}`);

                    }

                }

            }

        }

        // Return result
        return result;

    }

    /** Public parameters
     ******************************************************
     */

    /**
     * Get Mode
     * 
     * Return current backend mode used by the cache manager
     * 
     * @returns {'cache'|'localforage'}
     */
    public getMode = (): 'cache'|'localforage' => {

        // Return current mode
        return this._mode;

    }

    /** Private parameters
     ******************************************************
     */

    /**
     * Key
     * 
     * @param page 
     * @param hash 
     * @returns 
     */
    private _key = (page:string, hash?:string):string|Request => {

        // Set key
        const key = `${this._prefix}${page}${hash ? `-${hash}` : ''}`;

        // Return depending mode
        return this._mode === 'cache' ? new Request(key) : key;

    }

}

/** Interface
 ******************************************************
 */

/* Cache Page */
interface CachedPage {
    hash:string;
    content:string;
}
