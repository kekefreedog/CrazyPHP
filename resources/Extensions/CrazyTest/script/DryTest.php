<?php declare(strict_types=1);
/**
 * Basic Test
 *
 * Methods of Crazy Test Extension
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2025 Kévin Zarshenas
 */
namespace Tests\Basic;

/**
 * Dependances
 */
use PHPUnit\Framework\TestCase;

/**
 * Dry test
 *
 * Methods for test php test
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class DryTest extends TestCase {

    /**
     * Test True
     * 
     * @return void
     */
    public function testTrue():void {

        # Test true
        $this->assertTrue(true);

    }

}