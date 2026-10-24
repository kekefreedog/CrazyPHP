<?php declare(strict_types=1);
/**
 * End-to-end Scenario
 *
 * One combination of `crazy new project` and `crazy docker new` answers.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\E2E\Scenario;

/**
 * Dependencies
 */
use CrazyPHP\Model\App\Create;

/**
 * Scenario
 *
 * Holds the answers of both wizards and exposes them as flat parameters for filters and checks.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class Scenario {

    /**
     * Constructor
     *
     * @param array $create Answers of `crazy new project`, keyed by question name
     * @param array $docker Answers of `crazy docker new`, keyed by question name
     */
    public function __construct(
        public readonly array $create,
        public readonly array $docker,
    ){}

    /** Public Methods
     ******************************************************
     */

    /**
     * Parameters
     *
     * Flat, normalized values: project questions keep their name (`type`, `router`...), each
     * database engine becomes `database.<engine>` = yes|no, Docker questions are prefixed `server.`.
     * Questions hidden by the wizard read `none`, as `Model\App\Create` normalizes them.
     *
     * @return array<string, string>
     */
    public function parameters():array {

        # Prepare result
        $result = [];

        # Iteration of project answers
        foreach($this->create as $name => $value){

            # Free-text answers do not identify a branch
            if($value === null)

                # Next answer
                continue;

            # Expand engines so each one pairs with the other choices
            if(is_array($value)){

                # Iteration of offered engines
                foreach(self::engines() as $engine)

                    # Flag selected engines
                    $result["$name.$engine"] = in_array($engine, $value, true) ? "yes" : "no";

                # Next answer
                continue;

            }

            # Keep the choice
            $result[$name] = (string) $value;

        }

        # Iteration of questions hidden for some project types
        foreach(["authentication", "router", "frontend"] as $name)

            # Hidden questions read as none
            $result[$name] ??= "none";

        # Iteration of offered engines
        foreach(self::engines() as $engine)

            # Undefined engines read as not selected
            $result["database.$engine"] ??= "no";

        # Iteration of Docker answers
        foreach($this->docker as $name => $value)

            # Keep branch choices only
            if($value !== null)

                # Prefix Docker parameters
                $result["server.$name"] = (string) $value;

        # HTTP never uses the HTTPS proxy branch
        $result["server.proxy"] ??= "no";

        # Keep a stable order
        ksort($result);

        # Return result
        return $result;

    }

    /**
     * Coverage
     *
     * Parameters used for pairwise selection. The Docker answers are joined into one `server`
     * value so every stack variant (e.g. nginx + managed HTTPS through Caddy) is generated, and
     * each one is paired with the project choices.
     *
     * @return array<string, string>
     */
    public function coverage():array {

        # Prepare result and stack
        $result = [];
        $server = [];

        # Iteration of flat parameters
        foreach($this->parameters() as $name => $value){

            # Split project and Docker parameters
            if(str_starts_with($name, "server.")){

                # Collect the stack part
                $server[] = $value;

            }else{

                # Keep the project parameter
                $result[$name] = $value;

            }

        }

        # Join the Docker stack
        $result["server"] = implode("/", $server);

        # Return result
        return $result;

    }

    /**
     * Get
     *
     * @param string $name Flat parameter name
     * @return string|null
     */
    public function get(string $name):?string {

        # Return the parameter
        return $this->parameters()[$name] ?? null;

    }

    /**
     * Databases
     *
     * @return string[]
     */
    public function databases():array {

        # Return selected engines
        return array_values((array) ($this->create["database"] ?? []));

    }

    /**
     * Has Frontend
     *
     * @return bool
     */
    public function hasFrontend():bool {

        # Frontend questions only exist for browser projects
        return $this->get("frontend") !== "none";

    }

    /**
     * Id
     *
     * Short, stable and usable as a directory and Compose project name.
     *
     * @return string
     */
    public function id():string {

        # Readable prefix
        $type = preg_replace("/[^a-z0-9]+/", "", $this->get("type") ?? "x");

        # Return prefix plus a hash of every parameter
        return "e2e-$type-".substr(md5(json_encode($this->parameters())), 0, 8);

    }

    /**
     * Label
     *
     * @return string
     */
    public function label():string {

        # Get databases
        $databases = implode("+", $this->databases()) ?: "no-db";

        # Return short description for PHPUnit output
        return implode(" ", [
            "php".$this->get("php_version"),
            $this->get("type"),
            $databases,
            "auth:".$this->get("authentication"),
            "router:".$this->get("router"),
            "front:".$this->get("frontend"),
            "|",
            $this->get("server.type"),
            $this->get("server.security").($this->get("server.proxy") === "yes" ? "+proxy" : ""),
        ]);

    }

    /**
     * Create Options
     *
     * Named options for `Model\App\Create`, with the project name set to the scenario id.
     *
     * @return array
     */
    public function createOptions():array {

        # Keep answered questions only
        $options = array_filter($this->create, static fn($value) => $value !== null);

        # Return options with a unique project name
        return [
            "name"          => $this->id(),
            "description"   => $this->label(),
        ] + $options;

    }

    /**
     * Docker Options
     *
     * @param int $httpPort Free host port
     * @param int $httpsPort Free host port
     * @return array
     */
    public function dockerOptions(int $httpPort, int $httpsPort):array {

        # Keep answered questions only
        $options = array_filter($this->docker, static fn($value) => $value !== null);

        # Return options with free ports
        return $options + [
            "hostname"      => "localhost",
            "http_port"     => $httpPort,
            "https_port"    => $httpsPort,
        ];

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Engines
     *
     * @return string[]
     */
    public static function engines():array {

        # Iteration of project questions
        foreach(Create::getRequiredValues() as $field)

            # Find the database question
            if($field["name"] === "database")

                # Return its choices
                return array_map("strval", array_keys($field["select"]));

        # No database question
        return [];

    }

}
