<?php declare(strict_types=1);
/**
 * Exception
 *
 * Exeption class for manipulate errors
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Exception;

/**
 * Dependances
 */
use League\CLImate\CLImate;
use Exception;

/**
 * MongodbException
 *
 * Exception for catch error from mongodb preparation
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MongodbException extends Exception {

    /** Variables
     ******************************************************
     */

    # Exception message
    protected $message = 'Unknown exception';

    # User-defined exception code                       
    protected $code = 0;

    # Source of the error LuckyPHP or App or Vendor
    public $source = null;

    /** Public methods
     ******************************************************
     */

    /**
     * Get Message For Terminal
     * 
     * @return void
     */
    public function getMessageForTerminal():void {

        # New climate
        $climate = new CLImate();

        # Message
        $climate
            ->br()
            ->out("〜 Code Error : ".$this->getCode())
            ->out("〜 ".$this->getMessage())
            ->br();
        ;

        # Check code is in CODE_TO_MESSAGE
        if(array_key_exists($this->getCode(), self::CODE_TO_MESSAGE)){

            # Message
            $climate
                ->green("🟢 ".self::CODE_TO_MESSAGE[$this->getCode()]["message"])
            ;

            # Check if exit
            if(self::CODE_TO_MESSAGE[$this->getCode()]["exit"] ?? false)

                # Exit script
                exit();

        }

    }

    /** Public constant
     ******************************************************
     */

    /** @const array CODE_TO_MESSAGE */
    public const CODE_TO_MESSAGE = [
        255 =>  [
            "message"   =>  "It's fine. First time you are trying to up docker you have to execute the current command again and everything should work well :".PHP_EOL."<white>`php vendor/kzarshenas/crazyphp/bin/CrazyDocker up`</white>",
            "exit"      =>  true
        ]
    ];

}