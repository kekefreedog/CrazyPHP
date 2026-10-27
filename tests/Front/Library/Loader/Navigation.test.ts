/**
 * Navigation Test
 *
 * Regression tests for proxy URLs and authentication redirects.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Page from "../../../../src/Front/Library/Loader/Page";
import {afterEach, beforeEach, it, mock} from "node:test";
import State from "../../../../src/Front/Library/State";
import assert from "node:assert/strict";
import {JSDOM} from "jsdom";

/** @var dom Isolated browser document */
let dom:JSDOM;

/** @var originalWindow Existing browser global */
const originalWindow = globalThis.window;

/** @var originalDocument Existing document global */
const originalDocument = globalThis.document;

/** @var originalRequest Native request constructor */
const originalRequest = globalThis.Request;

// Prepare a clean browser document for each navigation.
beforeEach(() => {

    // Create the initial loading screen.
    dom = new JSDOM('<div id="crazy-root">Loading</div>', {url: "https://toolkit.test/"});

    // Install the browser window.
    globalThis.window = dom.window as unknown as Window & typeof globalThis;

    // Install the browser document.
    globalThis.document = dom.window.document;

    // Match browser resolution of relative fetch requests.
    globalThis.Request = class extends originalRequest {

        /**
         * Resolve request input against the document URL.
         */
        public constructor(input:RequestInfo|URL, init?:RequestInit){

            // Construct the browser-style request.
            super(typeof input === "string" ? new URL(input, window.location.href) : input, init);

        }

    };

    // Supply the framework services used by the loader.
    window.Crazyobject = {
        state: {globalStore: {iterate: async () => undefined}},
        currentPage: {get: () => null, set: () => undefined},
        hash: {get: () => "test"},
    } as unknown as typeof window.Crazyobject;

});

// Restore the environment after each test.
afterEach(() => {

    // Restore all replaced methods.
    mock.restoreAll();

    // Restore the native request constructor.
    globalThis.Request = originalRequest;

    // Close the isolated document.
    dom.window.close();

    // Restore the browser global.
    globalThis.window = originalWindow;

    // Restore the document global.
    globalThis.document = originalDocument;

});

// Verify internal route URLs remain usable behind a reverse proxy.
it("uses the browser origin for named routes and preserves path, query and fragment", async () => {

    // Return a route containing an internal proxy hostname.
    mock.method(globalThis, "fetch", async () => Response.json({results: [{name: "Home", path: "http://host.docker.internal:444/en/?view=list#projects"}]}));

    // Resolve the route.
    const options = await Page.loadUrl({name: "Home"});

    // Verify the public URL.
    assert.equal(String(options.url), "https://toolkit.test/en/?view=list#projects");

    // Verify the resolved URL can be written to browser history.
    await Page.updateUrl(options);

    // Verify browser navigation retained the public origin.
    assert.equal(window.location.href, "https://toolkit.test/en/?view=list#projects");

    // Capture a new-tab navigation.
    const open = mock.method(window, "open", () => null);

    // Verify new-tab navigation terminates the current loader.
    await assert.rejects(Page.openNewTab({...options, openInNewTab: true}));

    // Verify URL objects remain supported for new tabs.
    assert.deepEqual(open.mock.calls[0].arguments, ["https://toolkit.test/en/?view=list#projects", "_blank"]);

});

// Preserve explicit caller URLs instead of rewriting arbitrary external targets.
it("preserves explicitly supplied URLs", async () => {

    // Prepare an explicit target.
    const url = new URL("https://external.test/page");

    // Resolve the supplied target.
    const options = await Page.loadUrl({name: "External", url});

    // Verify only Router API results are normalized.
    assert.equal(options.url, url);

});

/**
 * Check Redirect
 *
 * Exercise the real request, response-event and page-loader flow.
 *
 * @param entries Persistent global route arguments
 * @param argumentsOverride Explicit redirect arguments
 * @return Completed redirect options
 */
async function checkRedirect(entries:Array<[string, string]>, argumentsOverride?:Record<string, string>){

    // Prepare result.
    let result:LoaderPageOptions = {};

    // Track pages allowed to render.
    const rendered:string[] = [];

    // Capture the redirect route arguments.
    let redirectQuery = new URL(window.location.href);

    // Model localForage iteration, including its early-return behavior.
    window.Crazyobject.state.globalStore = {
        iterate: async(callback:(value:string, key:string) => unknown) => {

            // Prepare the iterator result.
            let iterationResult:unknown;

            // Visit each stored argument.
            for(const [key, value] of entries){

                // Invoke the iterator.
                iterationResult = callback(value, key);

                // Stop only when the callback explicitly returns a value.
                if(iterationResult !== undefined)

                    // Match localForage early termination.
                    break;

            }

            // Return the iterator result.
            return iterationResult;

        },
    } as unknown as typeof window.Crazyobject.state.globalStore;

    // Supply the Home authentication response and Login route/state.
    mock.method(globalThis, "fetch", async(request:Request) => {

        // Prepare the response.
        let response:Response;

        // Read the requested resource.
        const url = new URL(request.url);

        // Resolve the destination route.
        if(url.pathname === "/api/v2/Router/filter"){

            // Record all redirect arguments.
            redirectQuery = url;

            // Return a route behind a different proxy origin.
            response = Response.json({results: [{name: "Login", path: "http://internal:8080/en/login/"}]});

        }else if(url.pathname === "/en/login/"){

            // Return the destination state.
            response = Response.json({login: true});

        }else{

            // Redirect the unauthenticated Home request.
            response = Response.json({_events: [{type: "redirect", name: "Login", arguments: argumentsOverride}]});

        }

        // Return the response.
        return response;

    });

    // Skip unrelated script, theme and partial services.
    for(const method of ["loadPageCacheManager", "loadScript", "updateTitle", "cleanPotentialExisitingPartials", "loadStyle", "registerInHistory", "applyColorSchema", "scanPartials", "loadOnReadyScript"] as const)

        // Preserve the navigation options through unrelated stages.
        mock.method(Page, method, async(options:LoaderPageOptions) => options);

    // Observe rendering without requiring template assets.
    mock.method(Page, "loadContent", async(options:LoaderPageOptions) => {

        // Record the rendered page.
        rendered.push(options.name!);

        // Return the options.
        return options;

    });

    // Observe the final destination.
    mock.method(Page, "setCurrentPage", async(options:LoaderPageOptions) => {

        // Save the completed navigation.
        result = options;

        // Return the options.
        return options;

    });

    // Capture unexpected loader errors.
    const errors = mock.method(console, "error", () => undefined);

    // Preserve existing Home state to detect rejected payload writes.
    State.set().page("Home", {previous: true});

    // Start a Home navigation with its resolved URL.
    await new Page({name: "Home", url: new URL("https://toolkit.test/en/")});

    // Allow the asynchronous redirect chain to complete.
    for(let attempt = 0; attempt < 100 && !result.name; attempt++)

        // Yield to promise continuations and document-ready events.
        await new Promise(resolve => setTimeout(resolve, 5));

    // Verify the destination completed.
    assert.equal(result?.name, "Login");

    // Verify Home never rendered its redirect-only state.
    assert.deepEqual(rendered, ["Login"]);

    // Verify the discarded response did not replace Home state.
    assert.deepEqual(State.get().page("Home"), {previous: true});

    // Verify the visible URL belongs to Login.
    assert.equal(window.location.href, "https://toolkit.test/en/login/");

    // Verify expected cancellation was not logged as an error.
    assert.equal(errors.mock.callCount(), 0);

    // Verify every stored argument reached the redirect request.
    for(const [key, value] of entries)

        // Prefer explicit redirect values over global defaults.
        assert.equal(redirectQuery?.searchParams.get(`options[arguments][${key}]`), argumentsOverride?.[key] ?? value);

    // Return result.
    return result;

}

// Verify first-load redirects work before global storage is populated.
it("reaches Login without rendering Home when global storage is empty", async () => {

    // Exercise a fresh session.
    await checkRedirect([]);

});

// Verify route arguments are complete and explicit values win.
it("reads every global redirect argument and preserves explicit overrides", async () => {

    // Exercise multiple stored arguments and an explicit language.
    await checkRedirect([["project", "demo"], ["language", "fr"]], {language: "en"});

});

// Verify opening another tab does not cancel the current page.
it("accepts page state containing a new-tab redirect", async () => {

    // Capture the separate tab request.
    const open = mock.method(window, "open", () => null);

    // Return state containing a separate-tab redirect.
    mock.method(globalThis, "fetch", async () => Response.json({content: "Home", _events: [{type: "redirect", url: "https://external.test/", target: "_blank"}]}));

    // Load the current page state.
    const options = await Page.loadPageState({name: "Home", url: new URL("https://toolkit.test/en/")});

    // Verify the current state remains accepted.
    assert.equal(options.status?.hasState, true);

    // Verify the current page data was stored.
    assert.equal(State.get().page("Home").content, "Home");

    // Verify the separate destination was opened.
    assert.equal(open.mock.callCount(), 1);

});
