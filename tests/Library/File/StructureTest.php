<?php declare(strict_types=1);
/**
 * Test File
 *
 * Test File Classes
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Library\File\Yaml;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Structure Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class StructureTest extends TestCase{

    /** Public constants method
     ******************************************************
     */

    /**
     * Root path of the test
     */
    public const RELATIVE_ROOT_PATH = __DIR__."/../../.cache/structure/";

    /**
     * Path of the template
     */
    public const RELATIVE_TEMPLATE_PATH = __DIR__."/../../../resources/Yml/Structure.yml";

    /** Public method
     ******************************************************
     */
    /**
     * Prepare Cache
     */
    public function prepareEnv():void {

        # Setup env
        Env::set([
            # App root for composer class
            "app_root"      =>  self::RELATIVE_ROOT_PATH,
            "crazyphp_root" =>  getcwd(),
            "phpunit_test"  =>  true,
        ]);

    }

    /**
     * Test Tree Folder Generator
     * 
     * @return void
     */
    public function testTreeFolderGeneratorCreate():void {

        # Check tmp directory already exists
        if(!is_dir(self::RELATIVE_ROOT_PATH))

            # Make tmp directory
            mkdir(self::RELATIVE_ROOT_PATH, 0777, true);

        # New instance
        $structure = new Structure(
            self::RELATIVE_ROOT_PATH,
            self::RELATIVE_TEMPLATE_PATH
        );

        # Run creation of structure
        $structure->run();

        # Get structure created
        $structureCreated = Structure::getFileTreeSimple(self::RELATIVE_ROOT_PATH);

        # Get structure expected
        $structureExpected = Structure::getFileTreeSimple($structure->getStructure());

        # Check created and expected
        $this->assertEquals($structureCreated, $structureExpected);

    }    
    
    /**
     * Test Tree Folder Deletion
     * 
     * @depends testTreeFolderGeneratorCreate
     * 
     * @return void
     */
    public function testTreeFolderGeneratorDelete():void {

        # Check tmp directory already exists
        if(!is_dir(self::RELATIVE_ROOT_PATH))

            # Make tmp directory
            mkdir(self::RELATIVE_ROOT_PATH, 0777, true);

        # New instance
        $structure = new Structure(
            self::RELATIVE_ROOT_PATH,
            self::RELATIVE_TEMPLATE_PATH,
            "delete"
        );

        # Run deletion of structure
        $structure->run();

        # Get item in root path
        $result = scandir(self::RELATIVE_ROOT_PATH);

        # Remove dots
        $result = array_filter($result, fn($e) => !in_array($e, [".", ".."]));

        # Check folder is empty
        $this->assertEmpty($result);

    }

    /**
     * Test structure create
     * 
     * @return void
     */
    public function testStructureCreate():void {

        # Prepare env
        $this->prepareEnv();

        # File path of structure yaml
        $filePath = File::path("@crazyphp_root/resources/Docker/Structure.yml");

        # Get structure
        $structure = Yaml::open($filePath);

        # Create structure
        Structure::create($structure);

        # Assert
        $this->assertTrue(true);
 
    }

    /**
     * Test structure check
     * 
     * @depends testStructureCreate
     */
    public function testStructureCheck():void {

        # We supposed env are already set

        # File path of structure yaml
        $schema = File::path("@crazyphp_root/resources/Docker/Structure.yml");

        # Create structure
        $result = Structure::check($schema);

        # Assert
        $this->assertTrue($result);

    }

    /**
     * Test structure delete
     */

}