<?php declare(strict_types=1);
/**
 * Test System Clock
 *
 * Verify live readings and timezone selection through lcobucci clocks.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\Time;

/**
 * Dependencies
 */
use Lcobucci\Clock\Clock as LcobucciClock;
use CrazyPHP\Library\Time\SystemClock;
use CrazyPHP\Library\Time\DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use DateTimeImmutable;
use DateTimeZone;

/**
 * System Clock Test
 *
 * Exercise explicit and default timezone behavior.
 */
class SystemClockTest extends TestCase {

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Now Uses Selected Timezone
     *
     * @return void
     */
    public function testNowUsesSelectedTimezone():void {

        # Capture a reading between independent system time readings
        $clock = new SystemClock(new DateTimeZone("America/New_York"));
        $before = new DateTimeImmutable("now");
        $result = $clock->now();
        $after = new DateTimeImmutable("now");

        # Assert compatible clock and date types
        $this->assertInstanceOf(ClockInterface::class, $clock);
        $this->assertInstanceOf(LcobucciClock::class, $clock);
        $this->assertInstanceOf(DateTime::class, $result);

        # Assert the selected timezone and current instant
        $this->assertSame("America/New_York", $result->getTimezone()->getName());
        $this->assertGreaterThanOrEqual($before, $result);
        $this->assertLessThanOrEqual($after, $result);

    }

    /**
     * Test Timezone Factories
     *
     * @return void
     */
    public function testTimezoneFactories():void {

        # Preserve global timezone state
        $timezone = date_default_timezone_get();

        try{

            # Create clocks before the default timezone changes
            date_default_timezone_set("Asia/Tokyo");
            $default = new SystemClock();
            $system = SystemClock::fromSystemTimezone();
            $utc = SystemClock::fromUTC();
            date_default_timezone_set("Europe/Paris");

            # Assert each clock retains its selected timezone
            $this->assertSame("Asia/Tokyo", $default->now()->getTimezone()->getName());
            $this->assertSame("Asia/Tokyo", $system->now()->getTimezone()->getName());
            $this->assertSame("UTC", $utc->now()->getTimezone()->getName());

        }finally{

            # Restore the original timezone
            date_default_timezone_set($timezone);

        }

    }

}
