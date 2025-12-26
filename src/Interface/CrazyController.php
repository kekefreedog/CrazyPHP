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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Interface;

/**
 * Crazy Controller Interface
 * 
 * Interface for define compatible class with cli command (for create, edit, delete new entity)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface CrazyController {

    /**
     * GET
     * 
     * Get controller
     * 
     * @return void
     */
    public function GET();

}