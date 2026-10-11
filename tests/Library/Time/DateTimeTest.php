<?php declare(strict_types=1);
/**
 * Test DateTime
 *
 * Verify immutable CrazyPHP date factories and file timestamps.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\Time;

/**
 * Dependencies
 */
use CrazyPHP\Library\Time\DateTime;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DateTimeZone;

/**
 * DateTime Test
 *
 * Preserve date helper behavior when dates become immutable.
 */
class DateTimeTest extends TestCase {

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Setters Return New Dates
     *
     * @return void
     */
    public function testSettersReturnNewDates():void {

        # Create a fixed date and derive a new calendar value
        $original = new DateTime("2026-01-15 12:30:45", new DateTimeZone("UTC"));
        $result = $original->setDate(2024, 2, 29)->setTime(0, 0, 0);

        # Assert inherited setters preserve the custom immutable type
        $this->assertInstanceOf(DateTimeImmutable::class, $original);
        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertSame("2026-01-15 12:30:45", $original->format("Y-m-d H:i:s"));
        $this->assertSame("2024-02-29 00:00:00", $result->format("Y-m-d H:i:s"));

    }

    /**
     * Test Date Factories
     *
     * @return void
     */
    public function testDateFactories():void {

        # Read the existing helper and inherited factory
        $birthday = DateTime::kz();
        $parsed = DateTime::createFromFormat("!Y-m-d", "2024-02-29");

        # Assert factories preserve the custom date type and calendar value
        $this->assertInstanceOf(DateTime::class, $birthday);
        $this->assertSame("1995-07-25", $birthday->format("Y-m-d"));
        $this->assertInstanceOf(DateTime::class, $parsed);
        $this->assertSame("2024-02-29", $parsed->format("Y-m-d"));

        # Assert a missing file retains the existing fallback
        $this->assertEquals($birthday, DateTime::lastUpdateFile());

    }

    /**
     * Test Last Update File
     *
     * @return void
     */
    public function testLastUpdateFile():void {

        # Create an isolated file with a known modification time
        $path = tempnam(sys_get_temp_dir(), "crazyphp-clock-");
        $this->assertNotFalse($path);

        try{

            # Set a fixed modification timestamp
            $timestamp = 1700000000;
            $this->assertTrue(touch($path, $timestamp));
            clearstatcache(true, $path);
            $result = DateTime::lastUpdateFile($path);

            # Assert the immutable result preserves the file timestamp
            $this->assertInstanceOf(DateTime::class, $result);
            $this->assertInstanceOf(DateTimeImmutable::class, $result);
            $this->assertSame($timestamp, $result->getTimestamp());

        }finally{

            # Remove the temporary fixture
            unlink($path);

        }

    }

}
