<?php declare(strict_types=1);
/**
 * Model Batch API
 *
 * Authorize every batch item before executing any model mutation.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace CrazyPHP\Controller;

/**
 * Dependencies
 */
use Psr\Http\Message\ServerRequestInterface;
use CrazyPHP\Library\Router\Middleware;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Core\Controller;
use Exception;

/**
 * API V2 Batch
 *
 * Preserve per-item results while rejecting malformed or unauthorized batches before writes.
 */
class ApiV2Batch extends Controller {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Execute Batch
     *
     * Apply the same model middleware to each item as to individual CRUD requests.
     *
     * @param ServerRequestInterface $request Current HTTP request
     * @return void
     */
    public static function post($request):void {

        # Read the submitted batch before creating any model
        $items = self::getHttpRequestData();

        # Require a request and a nonempty batch collection
        if(!$request instanceof ServerRequestInterface || !is_array($items) || empty($items)){

            # Reject invalid batches before mutation
            throw new CrazyException("A nonempty model batch is required.", 400);

        }

        # Validate and authorize the entire batch before any item can write
        foreach($items as $item){

            # Require an explicit supported operation and model name
            if(
                !is_array($item) ||
                !is_string($item["entity"] ?? null) ||
                $item["entity"] === "" ||
                !in_array($item["type"] ?? null, [
                    "create",
                    "update",
                    "delete"
                ], true) ||
                ($item["type"] !== "delete" && !is_array($item["body"] ?? null)) ||
                ($item["type"] !== "create" && !is_string($item["id"] ?? null) && !is_int($item["id"] ?? null))
            ){

                # Reject malformed items without partial execution
                throw new CrazyException("Invalid model batch item.", 400);

            }

            # Represent the item's individual CRUD request for model middleware
            $itemRequest = $request
                ->withMethod([
                    "create"    =>  "POST",
                    "update"    =>  "PUT",
                    "delete"    =>  "DELETE"
                ][$item["type"]])
                ->withParsedBody($item["body"] ?? [])
                ->withAttribute("model.operation", $item["type"])
                ->withAttribute("model.id", $item["id"] ?? null)
            ;

            # Enforce declared model checks before loading any batch driver
            Middleware::runModelMiddleware($itemRequest, $item["entity"]);

        }

        # Collect the established per-item result envelope
        $state = self::ApiState();

        # Execute only fully authorized batches
        foreach($items as $key => $item){

            # Preserve per-item handling for runtime model failures
            try{

                # Construct the authorized model
                $model = self::Model($item["entity"]);

                # Execute the validated operation
                $content = match($item["type"]){
                    "create" => $model->create($item["body"]),
                    "update" => $model->updateById($item["id"], $item["body"]),
                    "delete" => $model->deleteById($item["id"]),
                };

                # Preserve the entity-keyed batch response
                $state->pushResults([
                    $key    =>  $content
                ], $item["entity"]);

            }catch(Exception $exception){

                # Preserve runtime errors in the batch response
                $state->pushException($exception);

            }

        }

        # Send the established API response envelope
        self::ApiResponse()
            ->setStatusCode()
            ->pushContent("results", $state->render())
            ->send()
        ;

    }

}
