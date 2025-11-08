<?php declare(strict_types=1);
/**
 * Test Json
 *
 * Test Json
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\Array;

/**
 * Dependances
 */;

use CrazyPHP\Library\String\Jwt;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Jwt test
 *
 * Methods for test interactions with jwt
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class JwtTest extends TestCase{

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
            "phpunit_test"      =>  true,
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

        # Reset env
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Is Valid Method
     * 
     * @return void
     */
    public static function testJwtGetInfo():void {

        # Fake token
        $fakeToken = 
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'.
            '.eyJzdWIiOiIxMjM0NTY3ODkwIiwidXNlcm5hbWUiOiJrZXZpbiIsInNjb3BlIjpbInJlYWQiLCJ3cml0ZSJdLCJleHAiOjQxMDI0NDQ4MDB9'.
            '.c2lnbmF0dXJl'
        ;

        # Expected
        $expected = [
            "header"    => [
                "alg"       =>  "HS256",
                "typ"       =>  "JWT"
            ],
            "payload"   => [
                "sub"       => 1234567890,
                "username"  => "kevin",
                "scope"     => [
                    0           =>  "read",
                    1           =>  "write"
                ],
                "exp"   => 4102444800
            ]
        ];

        # Set instance
        $instance = new Jwt($fakeToken);

        # Set info
        $info = $instance->get();

        # Assert
        static::assertEquals($expected, $info);

        # Get username
        $username = $instance->get("payload.username");

        # Assert
        static::assertEquals($expected["payload"]["username"], $username);

    }
    
    /** Public constants
     ******************************************************
     */

}