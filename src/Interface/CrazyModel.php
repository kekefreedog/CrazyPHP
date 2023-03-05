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
 * Crazy Model Interface
 * 
 * Interface for define compatible class with Model (based on mongo or other model driver)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
interface CrazyModel {

    /** Public methods | Create
     ******************************************************
     */
    
    /**
     * Create
     * 
     * @param array $data Data with attributes values to use for create item
     * @param ?array $options Optionnal options
     * @return array
     */
    public function create(array $data, ?array $options = null):array;

    /** Public methods | Id 
     ******************************************************
     */

    /**
     * Read By Id
     * 
     * @param string|int $id Id of the item you want read
     * @param ?array $options Optionnal options
     * @return array
     */
    public function readById(string|int $id, ?array $options = null):array;

    /**
     * Update By Id
     * 
     * @param string|int $id Id of the item you want update
     * @param array $data Data with attributes values to use for update
     * @param ?array $options Optionnal options
     * @return array
     */
    public function updateById(string|int $id, array $data, ?array $options = null):array;

    /**
     * Delete By Id
     * 
     * @param string|int $id Id of the item you want delete
     * @param ?array $options Optionnal options
     * @return array
     */
    public function deleteById(string|int $id, ?array $options = null):array;


    /** Public methods | Filters 
     ******************************************************
     */

    /**
     * Read With Filters
     * 
     * @param ?array $filters Filters to use for read items
     * @param null|array|string $sort Options to use for sort items read
     * @param ?array $group Options to use for group items read
     * @param ?array $options Optionnal options
     * @return array
     */
    public function readWithFilters(?array $filters, null|array|string $sort = null, ?array $group = null, ?array $options = null):array;

    /**
     * Count With Filters
     * 
     * @param ?array $filters Filters to use for read items
     * @param ?array $options Optionnal options
     * @return int
     */
    public function countWithFilters(?array $filters = null, ?array $options = null):int;

    /**
     * Update With Filters
     * 
     * @param array $data Data with attributes values to use for update
     * @param array $filters Filters to use for read itemsd
     * @param ?array $options Optionnal options
     * @return array
     */
    public function updateWithFilters(array $data, ?array $filters = null, ?array $options = null):array;

    /**
     * Delete With Filters
     * 
     * @param array $filters Filters to use for read items
     * @param ?array $options Optionnal options
     * @return array
     */
    public function deleteWithFilters(array $filters, ?array $options = null):array;


    /** Public methods | Sql 
     ******************************************************
     */

    /**
     * Create With Sql
     * 
     * @param string $sql Sql query to use for create item
     * @param ?array $options Optionnal options
     * @return array
     */
    public function createWithSql(string $sql, array $data, ?array $options = null):array;

    /**
     * Read With Sql
     * 
     * @param string $sql Sql query to use for read items
     * @param ?array $options Optionnal options
     * @return array
     */
    public function readWithSql(string $sql, ?array $options = null):array;

    /**
     * Update With Sql
     * 
     * @param string $sql Sql query to use for update items
     * @param array $data Data with attributes values to use for update
     * @param ?array $options Optionnal options
     * @return array
     */
    public function updateWithSql(string $sql, array $data, ?array $options = null):array;

    /**
     * Delete With Sql
     * 
     * @param string $sql Sql query to use for delete items
     * @param ?array $options Optionnal options
     * @return array
     */
    public function deleteWithSql(string $sql, ?array $options = null):array;


}