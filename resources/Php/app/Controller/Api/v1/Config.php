<?php declare(strict_types=1);
/**
 * App
 *
 * Workflow of your app
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace App\Controller\Api\V1;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Model\Config as ModelConfig;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\File\File;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Controller;

 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Config extends Controller {

    /**
     * Get
     * 
     * @param $request
     * @return void
     */
    public static function get($request):void {

        # Set parameters
        $paramters = self::getParametersUrl();

        # Check if parameters parameters
        if(empty($paramters)){

            # Declare name
            $names = [];

            # New finder
            $finder = new Finder();

            # Prepare finder
            $finder
                ->in(File::path(ModelConfig::DEFAULT_PATH))
            ;

            # Prepare config name
            foreach($finder as $file)

                # Push name of the file without extension
                $names[] = $file->getBasename('.' . $file->getExtension());

            # Get all configs
            $configs = FileConfig::get($names);

            # Set content
            $content["config"] = $configs;

        }else
        # Check if name
        if(isset($paramters["parameter"])){

            # Get config value
            $content[$paramters["name"]] = FileConfig::getValue($paramters["name"].".".$paramters["parameter"]);

        }else
        # Check name is set
        if(isset($paramters["name"])){

            # check if config given exists
            $content["config"] = FileConfig::get($paramters["name"]);

        }


        # Set response
        (new ApiResponse())
            ->pushContent("results", $content)
            ->pushContext()
            ->send();

    }

}