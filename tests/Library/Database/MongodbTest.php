<?php declare(strict_types=1);
/**
 * Test Database
 *
 * Test Database Classes
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\Database;

/**
 * Dependances
 */
use CrazyPHP\Library\Database\Driver\Mangodb;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Mongodb Test
 *
 * Methods for test Mongodb
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MongodbTest extends TestCase {
    
    /** Public constants
     ******************************************************
     */

    /** @const array SCHEMA */
    public const SCHEMA = [
        [
            'name'  => 'type',
            'label' => 'Entity type',
            'type' => 'VARCHAR',
            'required' => true,
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
            'required' => true,
        ],
        [
            'name' => 'attributes.sg_release_title',
            'label' => 'Release title of the project',
            'type' => 'VARCHAR',
        ],
        [
            'name' => 'attributes.image',
            'label' => 'Image of the show',
            'type' => 'STRING',
        ],
        [
            'name' => 'links.self',
            'label' => 'Links to Api of ShotGrid',
            'type' => 'VARCHAR',
        ],
    ];

    /** @const array SCHEMA */
    public const SCHEMA_BIS = [
        [
            'name'  => 'attributes.sg_department_1.Department.sg_division',
            'label' => 'Entity type',
            'type' => 'VARCHAR',
            # 'required' => true,
        ],
    ];

    /** @cont array EXPECTED */
    public const EXPECTED = [
        'validator' => [
            '$jsonSchema' => [
                'bsonType' => 'object',
                'properties' => [
                    'type' => [
                        'bsonType' => 'string',
                        'description' => 'Entity type must be a "string"',
                    ],
                    'id' => [
                        'bsonType' => 'int',
                        'description' => 'SG ID of the project must be a "int"',
                    ],
                    'attributes' => [
                        'bsonType' => 'object',
                        'properties' => [
                            'name' => [
                                'bsonType' => 'string',
                                'description' => 'Name of the project must be a "string"',
                            ],
                            'sg_release_title' => [
                                'bsonType' => 'string',
                                'description' => 'Release title of the project must be a "string"',
                            ],
                            'image' => [
                                'bsonType' => 'string',
                                'description' => 'Image of the show should be a "string"',
                            ],
                        ],
                        'required' => ['name'],
                    ],
                    'links' => [
                        'bsonType' => 'object',
                        'properties' => [
                            'self' => [
                                'bsonType' => 'string',
                                'description' => 'Links to Api of ShotGrid must be a "string"',
                            ],
                        ],
                    ],
                ],
                'required' => ['type', 'attributes'],
            ],
        ],
    ];

    /** @cont array EXPECTED */
    public const EXPECTED_BIS = [
        'validator' => [
            '$jsonSchema' => [
                'bsonType' => 'object',
                'properties' => [
                    'attributes' => [
                        'bsonType' => 'object',
                        'properties' => [
                            'sg_department_1' => [
                                'bsonType' => 'object',
                                'properties' => [
                                    'Department' => [
                                        'bsonType' => 'object',
                                        'properties' => [
                                            'sg_division' => [
                                                'bsonType' => 'string',
                                                'description' => 'Entity type must be a "string"',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
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
            "phpunit_test"      =>  true,
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
        $mongoSchema = Mangodb::convertToMongoSchema(static::SCHEMA);

        # Assert
        $this->assertEquals(self::EXPECTED, $mongoSchema);

    }

    /**
     * Test Page State
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testPageStateBis():void {

        # New schema
        $mongoSchema = Mangodb::convertToMongoSchema(static::SCHEMA_BIS);

        # Assert
        $this->assertEquals(self::EXPECTED_BIS, $mongoSchema);

    }

}