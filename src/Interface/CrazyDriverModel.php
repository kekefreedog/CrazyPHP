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
 * Crazy Driver Model Interface
 * 
 * Interface for define compatible class with Driver Model (based on mongo or other model driver...)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
interface CrazyDriverModel {

    /** Public methods | Parser
     ******************************************************
     */

    /**
     * Parse Id
     * 
     * @param string|int $id ID to parse
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseId(string|int $id, ?array $options = null):self;

    /**
     * 
     */
/*     public function parseFilters();

    public function parseEntity();

    public function parseData();

    public function parseOptions();

    public function parseGroupBy();

    public function parseSortBy();

    public function parsePage();

    public function parseSql(); */

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run
     * 
     * Return data with given information
     * 
     * @param bool $clearOptionsAfter
     * @return array
     */
    public function run(bool $clearOptionsAfter = true):array;

}