<?php declare(strict_types=1);
/**
 * Interface
 *
 * Interface of CrazyPHP
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Interface;

/**
 * Dependances
 */
use CrazyPHP\Interface\CrazyController;

/**
 * Crazy Controller Api 2 Interface
 * 
 * Interface for define compatible your controller with the api2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
interface CrazyApi2 extends CrazyController {

    /** Public Methods | Get
     ******************************************************
     */

    /**
     * Get All
     * 
     * Return all items of entity
     * 
     * @return void
     */
    public function getAll():void;

    /**
     * Get Id
     * 
     * Return item of entity by id
     * 
     * @return void
     */
    public function getId():void;

    /**
     * Get List
     * 
     * Return all filtered items of entity
     * 
     * @return void
     */
    public function getFilter():void;

    /**
     * Get Count
     * 
     * Return number of items of entity
     * 
     * @return void
     */
    public function getCount():void;

    /**
     * Get Last
     * 
     * Return last items of entity
     * 
     * @return void
     */
    public function getLast():void;

    /**
     * Get New From
     * 
     * Return new items of entity
     * 
     * @return void
     */
    public function getNewFrom():void;

    /** Public Methods | Fields
     ******************************************************
     */

    /**
     * Get Fields
     * 
     * Get all fields of entity
     * 
     * @return void
     */
    public function getFields():void;

    /** Public Methods | Create
     ******************************************************
     */

    /**
     * Post Create
     * 
     * Create new item of entity
     * 
     * @return void
     */
    public function postCreate():void;

    /** Public Methods | Delete
     ******************************************************
     */

    /**
     * Delete Delete
     * 
     * Delete item of entity
     * 
     * @return void
     */
    public function deleteDelete():void;

    /** Public Methods | Update
     ******************************************************
     */

    /**
     * Put Update
     * 
     * Update item of entity
     * 
     * @return void
     */
    public function putUpdate():void;

}