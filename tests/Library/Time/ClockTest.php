<?php declare(strict_types=1);
/**
 * Test Clock
 *
 * Verify PSR-20 compatibility and current immutable date readings.
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
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Time\Clock;
use PHPUnit\Framework\TestCase;
use Lcobucci\Clock\FrozenClock;
use Psr\Clock\ClockInterface;
use DateTimeImmutable;

/**
 * Clock Test
 *
 * Exercise the clock through its public contract.
 */
class ClockTest extends TestCase {

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Now Returns Current CrazyPHP Date
     *
     * @return void
     */
    public function testNowReturnsCurrentCrazyPhpDate():void {

        # Read the clock between independent system time readings
        $clock = new Clock();
        $before = new DateTimeImmutable("now");
        $result = $clock->now();
        $after = new DateTimeImmutable("now");

        # Assert the PSR interface and covariant return type
        $this->assertInstanceOf(ClockInterface::class, $clock);
        $this->assertInstanceOf(LcobucciClock::class, $clock);
        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertInstanceOf(DateTimeImmutable::class, $result);

        # Assert the reading represents the current calendar time
        $this->assertGreaterThanOrEqual($before, $result);
        $this->assertLessThanOrEqual($after, $result);

    }

    /**
     * Test Now Returns Independent Immutable Readings
     *
     * @return void
     */
    public function testNowReturnsIndependentImmutableReadings():void {

        # Read the clock and derive a different date
        $clock = new Clock();
        $first = $clock->now();
        $original = $first->format("Y-m-d H:i:s.uP");
        $modified = $first->modify("+1 day");
        $before = new DateTimeImmutable("now");
        $second = $clock->now();
        $after = new DateTimeImmutable("now");

        # Assert modifications preserve the original reading and custom type
        $this->assertInstanceOf(DateTime::class, $modified);
        $this->assertNotSame($first, $modified);
        $this->assertSame($original, $first->format("Y-m-d H:i:s.uP"));
        $this->assertGreaterThan($first, $modified);

        # Assert subsequent calls read the system clock again
        $this->assertNotSame($first, $second);
        $this->assertGreaterThanOrEqual($before, $second);
        $this->assertLessThanOrEqual($after, $second);

    }

    /**
     * Test Adapted Clock Preserves Time And Updates
     *
     * @return void
     */
    public function testAdaptedClockPreservesTimeAndUpdates():void {

        # Adapt a native lcobucci clock with subsecond precision
        $instant = new DateTimeImmutable("2026-10-11 12:34:56.123456 Europe/Paris");
        $source = new FrozenClock($instant);
        $clock = new Clock($source);
        $result = $clock->now();

        # Assert conversion preserves the full reading
        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertSame($instant->format("U.u"), $result->format("U.u"));
        $this->assertSame("Europe/Paris", $result->getTimezone()->getName());

        # Assert source changes remain visible through the adapter
        $source->adjustTime("+1 hour");
        $this->assertEquals($instant->modify("+1 hour"), $clock->now());
        $this->assertEquals($instant, $result);

    }

    /**
     * Test Now Uses Default Timezone
     *
     * @return void
     */
    public function testNowUsesDefaultTimezone():void {

        # Preserve the caller's timezone
        $timezone = date_default_timezone_get();

        try{

            # Read the clock with a non-UTC default timezone
            date_default_timezone_set("Asia/Tokyo");
            $result = (new Clock())->now();

            # Assert the configured timezone is preserved
            $this->assertSame("Asia/Tokyo", $result->getTimezone()->getName());

        }finally{

            # Restore global state even when an assertion fails
            date_default_timezone_set($timezone);

        }

    }

}
