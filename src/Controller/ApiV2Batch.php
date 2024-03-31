<?php declare(strict_types=1);
/**
 * Controller
 *
 * Collection of controllers
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Controller;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Controller;
use Exception;

/**
 * Api V2 Batch
 *
 * List all entities available
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ApiV2Batch extends Controller {
    
    /**
     * post
     * 
     * @return void
     */
    public static function post():void {

        # New api state
        $state = self::ApiState();

        # Get 
        $requestData = self::getHttpRequestData();

        # Check request
        if(empty($requestData))

            # Push error
            $state->pushError([
                "code"  =>  400,
                "type"  =>  "warn",
                "detail"=>  "Request empty"
            ]);

        else

            # Iteration request data
            foreach($requestData as $key => $request){

                # Case create
                if(($request["type"] ?? "") == "create"){

                    try{

                        # Declare content
                        $content = self::Model($request["entity"])
                            ->create($request["body"])
                        ;

                        # Push content in result
                        $state->pushResults([$key => $content], $request["entity"]);

                    }catch(Exception $e){

                        # Push error in state
                        $state->pushException($e);

                    }

                }else
                # Case delete
                if(($request["type"] ?? "") == "update"){

                    try{

                        # Declare content
                        $content = self::Model($request["entity"])
                            ->updateById($request["id"], $request["body"])
                        ;

                        # Push content in result
                        $state->pushResults([$key => $content], $request["entity"]);

                    }catch(Exception $e){

                        # Push error in state
                        $state->pushException($e);

                    }

                }else
                # Case delete
                if(($request["type"] ?? "") == "delete"){

                    try{

                        # Declare content
                        $content = self::Model($request["entity"])
                            ->deleteById($request["id"])
                        ;

                        # Push content in result
                        $state->pushResults([$key => $content], $request["entity"]);

                    }catch(Exception $e){

                        # Push error in state
                        $state->pushException($e);

                    }
                
                }else

                    # Push error
                    $state->pushError([
                        "code"  =>  400,
                        "type"  =>  "warn",
                        "detail"=>  "Request type n°$key is not supported with batch"
                    ]);

            }

        # Set response
        self::ApiResponse()
            ->setStatusCode()
            ->pushContent("results", $state->render())
            ->send();

    }

}