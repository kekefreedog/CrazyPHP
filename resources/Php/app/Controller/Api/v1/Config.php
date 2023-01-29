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
use CrazyPHP\Library\Time\DateTime;
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

        # Set
        $statutCode = 200;

        # Declare content
        $content = null;

        /** All config
         ******************************************************
         */

        # Check if parameters parameters
        if(empty($paramters)){

            # Declare name & last modified
            $names = [];
            
            /** @var DateTime|\DateTime|null */
            $lastModified = null;

            # New finder
            $finder = new Finder();

            # Prepare finder
            $finder
                ->in(File::path(ModelConfig::DEFAULT_PATH))
            ;

            # Prepare config name
            foreach($finder as $file){

                # Push name of the file without extension
                $names[] = $file->getBasename('.' . $file->getExtension());

                # Get last modified date
                $lastModifiedTemp = (new DateTime)->setTimestamp($file->getMTime());

                # Comparaison
                if($lastModified === null || $lastModifiedTemp > $lastModified)

                    # Set last modified
                    $lastModified = $lastModifiedTemp;

            }

            # Check if If-Modified-Since
            if(self::clientIsNotUpToDate($lastModified)){

                # Get all configs
                $configs = FileConfig::get($names);

                # Set content
                $content["config"] = $configs;

            }else

                # Set statut code
                $statutCode = 304;
            
        /** Specific config
        ******************************************************
        */

        }else
        # Check if name
        if(isset($paramters["parameter"])){

            # Set last modified date
            $lastModified = FileConfig::getLastModified($paramters["name"]);

            # Check if If-Modified-Since
            if(self::clientIsNotUpToDate($lastModified)){

                # Get config value
                $content[$paramters["name"]] = FileConfig::getValue($paramters["name"].".".$paramters["parameter"]);

            }else

            # Set statut code
            $statutCode = 304;
            
        /** Specific value of config
        ******************************************************
        */

        }else
        # Check name is set
        if(isset($paramters["name"])){

            # Set last modified date
            $lastModified = FileConfig::getLastModified($paramters["name"]);

            # Check if If-Modified-Since
            if(self::clientIsNotUpToDate($lastModified)){

                # check if config given exists
                $content["config"] = FileConfig::get($paramters["name"]);

            }else

                # Set statut code
                $statutCode = 304;

        }

        # Set response
        (new ApiResponse())
            ->addLastModified($lastModified)
            ->setStatusCode($statutCode)
            ->pushContent("results", $content)
            ->pushContext()
            ->send();

    }

}