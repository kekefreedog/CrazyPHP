<?php declare(strict_types=1);
/**
 * Test State
 *
 * Test State Classes
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependances
 */

use CrazyPHP\Library\Model\Schema;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Schema Test
 *
 * Methods for test schema
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class SchemaTest extends TestCase {
    
    /** Public constants
     ******************************************************
     */

    /** @const array SCHEMA */
    public const SCHEMA = [
        [
            'name' => 'type',
            'label' => 'Entity type',
            'type' => 'VARCHAR',
        ],
        [
            'name' => 'id',
            'label' => 'SG ID of the project',
            'type' => 'INT',
        ],
        [
            'name' => 'attributes.name',
            'label' => 'Name of the project',
            'type' => 'VARCHAR',
        ],
        [
            'name' => 'attributes.sg_release_title',
            'label' => 'Release title of the project',
            'type' => 'VARCHAR',
        ],
        [
            'name' => 'attributes.image',
            'label' => 'Image of the show',
            'type' => 'VARCHAR',
        ],
        [
            'name' => 'links.self',
            'label' => 'Links to Api of ShotGrid',
            'type' => 'VARCHAR',
        ],
    ];

    /** @const array ITEM */
    public const ITEM = [
        'type' => 'Project',
        'attributes' => [
            'name' => 'test',
            'image' => '',
            'sg_release_title' => 'test2',
        ],
        'relationships' => [],
        'id' => 1111,
        'links' => [
            'self' => '/api/v1/entity/projects/1111',
        ],
    ];

    /** @const array ITEM_FILTERED */
    public const ITEM_FILTERED = [
        'attributes' => [
            'image' => 'toto',
        ]
    ];

    /** @cont array EXPECTED */
    public const EXPECTED = [
        [
            'type' => 'Project',
            'id' => 1111,
            'attributes' => [
                'name' => 'test',
                'sg_release_title' => 'test2',
                'image' => '',
            ],
            'links' => [
                'self' => '/api/v1/entity/projects/1111',
            ],
        ],
    ];

    /** @cont array EXPECTED_FILTERED */
    public const EXPECTED_FILTERED = [
        [
            'attributes' => [
                'image' => 'toto',
            ],
        ],
    ];

    /** Public method | Preparation
     ******************************************************
     */

    /**
     * Set Up Before Class
     * 
     * This method is called before the first test of this test class is run.
     * 
     * @return void
     */
    public static function setUpBeforeClass():void {

        # Setup env
        Env::set([
            # App root for composer class
            "crazyphp_root"     =>  getcwd(),
            "config_location"   =>  "@crazyphp_root/resources/Yml"
        ]);

    }


    /**
     * Tear Down After Class
     * 
     * This method is called after the last test of this test class is run.
     * 
     * @return void
     */
    public static function tearDownAfterClass():void {

        # Reset env variables
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Page State
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testPageState():void {

        # New schema
        $schema = new Schema(self::SCHEMA, [self::ITEM], [
            "flatten"       =>  true,
        ]);

        # Get result
        $result = $schema->getResultSummary();

        # Assert
        $this->assertEquals(self::EXPECTED, $result);

    }

    /**
     * Test Skip Empty Value
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testSkipEmptyValue():void {

        # New schema
        $schema = new Schema(self::SCHEMA, [self::ITEM_FILTERED], [
            "flatten"       =>  true,
            "phpunit_test"  =>  true,
            "skipEmptyValue"=>  true
        ]);

        # Get result
        $result = $schema->getResultSummary();

        # Assert
        $this->assertEquals(self::EXPECTED_FILTERED, $result);

    }

}