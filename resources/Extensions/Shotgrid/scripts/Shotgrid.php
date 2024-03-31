<?php declare(strict_types=1);
/**
 * Driver
 *
 * Drivers of your CrazyPHP App
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  App\Driver\Model;

/**
 * Dependances
 */
use GuzzleHttp\Exception\BadResponseException;
use CrazyPHP\Interface\CrazyDriverModel;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Model\Context;
use CrazyPHP\Core\Model;
use GuzzleHttp\Client;

/**
 * Shotgrid
 *
 * Class for drive a model of type Shotgrid
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Shotgrid implements CrazyDriverModel {

    /** Private parameters
     ******************************************************
     */

    /** @param $credentials */
    private array $_credentials = [
        "host"          =>  null,
        "username"      =>  null,
        "password"      =>  null,
        "grant_type"    =>  "password",
        "tokens"        =>  null,
    ];

    /** @param $entity */
    private string $_entity;

    /** @param $body */
    private array $_body = [];

    /** @param $data */
    private array $_data = [];

    /** @param $id */
    private int|null $_id = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(...$inputs) {

        # Check inputs entity
        $this->_entity = $inputs["entity"] ?? Context::getParameters('entity');
 
        # Set name
        $this->_loadCredentials();

        # Mongo connection
        $this->_newConnection();

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Load Credentials
     */
    private function _loadCredentials():void {

        # New model
        $model = new Model("Settings");

        # Get content
        $content = $model
            ->readWithFilters(
                [],
                "DESC",
                null,
                [
                    "limit" =>  1
                ]
            )
        ;

        # Get first document on bson
        $document = $content[0];

        # Retrieve data from credentials
        $this->_credentials["host"] = $document->offsetGet('shotgrid_website');
        $this->_credentials["username"] = $document->offsetGet('shotgrid_login');
        $this->_credentials["password"] = $document->offsetGet('shotgrid_password');

    }

    private function _newConnection():void {        
        
        # Prepare url of shotgun
        $url = $this->_credentials["host"]."/api/v1/auth/access_token";

        # Build content of query
        $content = http_build_query([
            "username"      =>  $this->_credentials["username"],
            "password"      =>  $this->_credentials["password"],
            "grant_type"    =>  $this->_credentials["grant_type"]
        ]);

        # Prepare options
        $options = [
            'http' => [
                'header'  => 
                    "Accept: application/json\r\n".
                    "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => $content,
            ]
        ];

        # Get data of request
        $reponse = file_get_contents(
            $url, 
            false, 
            stream_context_create($options)
        );

        # Push shotgun config in auth
        $this->_credentials["tokens"] = json_decode($reponse, true);

    }

    /** @var bool $attributesAsValues Indicate if attributes is set as values in current schema */
    # private bool $attributesAsValues = false;

    /** Public mathods | Attributes
     ******************************************************
     */

    /**
     * Set Attributes As Values
     * 
     * Switch attributes to values
     * 
     * @return self
     */
    public function setAttributesAsValues():self {

        # Return self
        return $this;

    }

    /** Public methods | Parser
     ******************************************************
     */

    /**
     * Parse Id
     * 
     * @param string|int $id Id to parse
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseId(string|int $id, ?array $options = null):self {

        # Check id
        if($id && is_int($id) && $id !== 0){

            # Set _id
            $this->_id = $id;

        }

        # Return self
        return $this;

    }

    /**
     * Parse Filters
     * 
     * @param array $filters Filter to process
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseFilter(?array $filters, ?array $options = null):self {

        # check filters
        if($filters !== null && !empty($filters))

            # Set filters
            $this->_body["filters"] = $filters;

        
        # check filters
        if($filters !== null && !empty($filters))

            # Set filters
            $this->_body["filters"] = $filters;

        # Check options 
        if(isset($options["grouping"]) && !empty($options["grouping"]))
        
            # Set grouping
            $this->parseGroup($options["grouping"], $options);

        # Check summary_fields
        if(isset($options["summary_fields"]) && !empty($options["summary_fields"]))

            # Fill body
            $this->_body["summary_fields"] = $options["summary_fields"];

        # Check files
        if(isset($options["fields"]) && !empty($options["fields"]))

            # Fill body
            $this->_body["fields"] = $options["fields"];

        # Return self
        return $this;

    }

    /**
     * Parse Sort
     * 
     * @param null|array|string $sort Sort to process
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseSort(null|array|string $sort, ?array $options = null):self {

        # Check sort
        if(is_string($sort) && !empty($sort)){

            # Push sort
            $this->_body["sort"] = $sort;

        }

        # Return self
        return $this;

    }

    /**
     * Parse Group
     * 
     * @param array $group Group to process
     * @param ?array $options Optionnal options
     */
    public function parseGroup(?array $group, ?array $options = null):self {

        # check group
        if(!empty($group))

            # Set body
            $this->_body["grouping"] = $group;

        # Return self
        return $this;

    }

    /**
     * Parse Sql
     * 
     * @param string $sql Sql query
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseSql(string $sql, ?array $options = null):self {

        # Return self
        return $this;

    }

    /** Public methods | Ingester
     ******************************************************
     */

    /**
     * Ingest Data
     * 
     * Import data in current driver
     * 
     * @param array $data
     * @param ?array $options Optionnal options
     * @return self
     */
    public function ingestData(array $data, ?array $options = null):self {

        # Check data
        if(isset($data["data"]) && !empty($data["data"]))

            # Set data
            $this->_data = $data["data"];

        # Return self
        return $this;

    }

    /** Public methods | Pusher
     ******************************************************
     */

    /**
     * Push to trash
     * 
     * Put to trash current value
     * 
     * @param ?array $options Optionnal options
     * @param 
     */
    public function pushToTrash(?array $options = null):self {

        # Return self
        return $this;

    }

    /** Public methods | Execute
     ******************************************************
     */

    /**
     * Run
     * 
     * Return data with given information
     * 
     * @return array
     */
    public function run():array {

        # Set result
        $result = [];

        # Check integer
        $this->_body = Arrays::convertStringsToIntegers($this->_body);

        # New client
        $client = new Client();

        # Update and check _data
        if(!empty($this->_data) && $this->_id){

            # Query
            $query = $this->_credentials["host"]."/api/v1/entity/".$this->_entity."/".$this->_id;

            # Prepare header
            $header = [
                'Content-Type'  =>  'application/json',
                'Accept'        =>  'application/json',
                'Authorization' =>  'Bearer '.$this->_credentials["tokens"]["access_token"]
            ];

            # Try
            try {

                # Get response
                $response = $client->request(
                    'PUT',
                    $query,
                    [
                        'headers'   =>  $header,
                        'json'      =>  $this->_data,
                    ]
                );

                # Set result
                $result = $response->getBody()->getContents();

            }catch(BadResponseException $e){

                # Set result
                # $result = $e->getMessage();
                $result = $e->getResponse()->getBody()->getContents(); 

            }

            # Decode result
            $result = json_decode($result, true);

        }else
        # Create | Check _data
        if(!empty($this->_data)){

            # Query
            $query = $this->_credentials["host"]."/api/v1/entity/".$this->_entity;

            # Prepare header
            $header = [
                'Content-Type'  =>  'application/json',
                'Accept'        =>  'application/json',
                'Authorization' =>  'Bearer '.$this->_credentials["tokens"]["access_token"]
            ];

            # Try
            try {

                # Get response
                $response = $client->request(
                    'POST',
                    $query,
                    [
                        'headers'   =>  $header,
                        'json'      =>  $this->_data,
                    ]
                );

                # Set result
                $result = $response->getBody()->getContents();

            }catch(BadResponseException $e){

                # Set result
                # $result = $e->getMessage();
                $result = $e->getResponse()->getBody()->getContents(); 

            }

            # Decode result
            $result = json_decode($result, true);

        }else
        # Check if summary
        if(isset($this->_body["summary_fields"]) && !empty($this->_body["summary_fields"])){

            # Query
            $query = $this->_credentials["host"]."/api/v1/entity/".$this->_entity."/_summarize";

            # Prepare header
            $header = [
                'Content-Type'  =>    (array_key_exists("logical_operator", $this->_body["filters"] ?? []))
                ? 'application/vnd+shotgun.api3_hash+json'
                : 'application/vnd+shotgun.api3_array+json',
                'Accept'        =>  'application/json',
                'Authorization' =>  'Bearer '.$this->_credentials["tokens"]["access_token"]
            ];

            # Try
            try {

                # Get response
                $response = $client->request(
                    'POST',
                    $query,
                    [
                        'headers'   =>  $header,
                        'json'      =>  $this->_body,
                    ]
                );

                # Set result
                $result = $response->getBody()->getContents();

            }catch(BadResponseException $e){

                # Set result
                # $result = $e->getMessage();
                $result = $e->getResponse()->getBody()->getContents(); 

            }

            # Decode result
            $result = json_decode($result, true);

        }else
        if(isset($this->_body["fields"]) && !empty($this->_body["fields"])){

            # Previous result
            $previousResult = [];

            # Set page number
            $pageNumber = 1;

            # First request
            $firstRequest = true;

            # Page size
            $pageSize = 500;

            # Do
            do{

                # Query
                $query = $this->_credentials["host"]."/api/v1/entity/".$this->_entity."/_search?page[number]=$pageNumber&page[size]=$pageSize";

                # Prepare header
                $header = [
                    'Content-Type'  =>  (array_key_exists("logical_operator", $this->_body["filters"] ?? []))
                        ? 'application/vnd+shotgun.api3_hash+json'
                        : 'application/vnd+shotgun.api3_array+json',
                    'Accept'        =>  'application/json',
                    'Authorization' =>  'Bearer '.$this->_credentials["tokens"]["access_token"]
                ];

                # Try
                try {

                    # Get response
                    $response = $client->request(
                        'POST',
                        $query,
                        [
                            'headers'   =>  $header,
                            'json'      =>  $this->_body,
                        ]
                    );

                    # Set result
                    $result = $response->getBody()->getContents();

                }catch(BadResponseException $e){

                    # Set result
                    # $result = $e->getMessage();
                    $result = $e->getResponse()->getBody()->getContents(); 

                }

                # Check page number
                if($firstRequest){

                    # Decode result
                    $previousResult = $result = json_decode($result, true);

                }else
                if(isset($previousResult["data"])){

                    # Decode result
                    $result = json_decode($result, true);

                    # Merge data
                    $result["data"] = array_merge($previousResult["data"], $result["data"]);

                }else{

                    # Decode result
                    $result = json_decode($result, true);

                }

                # Increment page
                $pageNumber++;

                # Set $firstRequest
                $firstRequest = false;


            }while(
                !empty($result["data"]) &&
                is_array($result["data"] ?? false) &&
                (
                    count($result["data"]) == $pageSize ||
                    count($result["data"]) % $pageSize == 0
                )
            );

        }else{

            # Query
            $query = $this->_credentials["host"]."/api/v1/entity/".$this->_entity;

            # Prepare header
            $header = [
                #'Content-Type'  =>  'application/vnd+shotgun.api3_array+json',
                'Accept'        =>  'application/json',
                'Authorization' =>  'Bearer '.$this->_credentials["tokens"]["access_token"]
            ];

            # Try
            try {

                # Get response
                $response = $client->request(
                    'GET',
                    $query,
                    [
                        'headers'   =>  $header,
                        'json'      =>  $this->_body,
                    ]
                );

                # Set result
                $result = $response->getBody()->getContents();

            }catch(BadResponseException $e){

                # Set result
                # $result = $e->getMessage();
                $result = $e->getResponse()->getBody()->getContents(); 

            }

            # Decode result
            $result = json_decode($result, true);
            
        }

        # Return self
        return $result;

    }

    /**
     * Count
     * 
     * Return counted data with given information
     * 
     * @return int
     */
    public function count():int {

        # Set result
        $result = 0;

        # Check entity
        # $query = $this->_credentials["host"]."/api/v1/entity/".$this->_entity;
        $query = $this->_credentials["host"]."/api/v1/entity/".$this->_entity."/_summarize";

        # Prepare header
        /* $header = [
            'Accept'        =>  'application/json',
            'Authorization' =>  'Bearer '.$this->_credentials["tokens"]["access_token"]
        ]; */

        # Prepare header
        $header = [
            'Content-Type'  =>  'application/vnd+shotgun.api3_array+json',
            'Accept'        =>  'application/json',
            'Authorization' =>  'Bearer '.$this->_credentials["tokens"]["access_token"]
        ];

        # Check summary_fields
        if(!isset($this->_body["summary_fields"]) || empty($this->_body["summary_fields"]))

            # Set field in body
            $this->_body["summary_fields"] = [
                [
                    "field" =>  "id",
                    "type"  =>  "count"
                ]
            ];

        # Check integer
        $this->_body = Arrays::convertStringsToIntegers($this->_body);

        # New client
        $client = new Client();

        # Try
        try {

            # Get response
            $response = $client->request(
                'POST',
                $query,
                [
                    'headers'   =>  $header,
                    'json'      =>  $this->_body,
                ]
            );

            # Set result
            $result = $response->getBody()->getContents();

        }catch(BadResponseException $e){

            # Set result
            # $result = $e->getMessage();
            $result = $e->getResponse()->getBody()->getContents(); 

        }

        # Decode result
        $result = json_decode($result, true);

        # Check if data summaries id
        if(isset($result["data"]["summaries"]["id"]))

            # Set result 
            $result = $result["data"]["summaries"]["id"];

        # Return self
        return $result;

    }

    /** Public methods | tests
     ******************************************************
     */

    /**
     * Force Summary
     * 
     * Use for test for force summary argument value
     * 
     * @param null|bool|array $input Summary state
     * @return self
     */
    public function forceSummary(null|bool|array $input = true):self {
        
        if(!empty($input))

            # Fill body
            $this->_body["summary_fields"] = $input;

        # Return self
        return $this;

    }

    /** Private methods | Process
     ******************************************************
     */

    /**
     * Page State Process
     * 
     * Process result (input) for Page State by adding _metadata info...
     * 
     * @param array $input
     * @return array
     */
    public function _pageStateProcess(array $input):array {

        # Set result
        $result = $input;

        # Return result
        return $result;

    }

    /** Private contant
     ******************************************************
     */

}