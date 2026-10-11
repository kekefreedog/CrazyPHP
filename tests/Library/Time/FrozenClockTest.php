<?php declare(strict_types=1);
/**
 * Test Frozen Clock
 *
 * Verify fixed readings, explicit adjustments and frozen clock factories.
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
use CrazyPHP\Library\Time\FrozenClock;
use CrazyPHP\Library\Time\DateTime;
use DateMalformedStringException;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use DateTimeImmutable;

/**
 * Frozen Clock Test
 *
 * Exercise controlled time without mutating returned dates.
 */
class FrozenClockTest extends TestCase {

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Frozen Time And Adjustments
     *
     * @return void
     */
    public function testFrozenTimeAndAdjustments():void {

        # Freeze a native immutable date with timezone and microseconds
        $instant = new DateTimeImmutable("2026-10-11 12:34:56.123456 Europe/Paris");
        $clock = new FrozenClock($instant);
        $original = $clock->now();

        # Assert compatible types and stable readings
        $this->assertInstanceOf(ClockInterface::class, $clock);
        $this->assertInstanceOf(LcobucciClock::class, $clock);
        $this->assertInstanceOf(DateTime::class, $original);
        $this->assertSame("2026-10-11 12:34:56.123456 Europe/Paris", $original->format("Y-m-d H:i:s.u e"));
        $this->assertEquals($original, $clock->now());

        # Assert modifying a returned date does not change the frozen clock
        $original->modify("+1 year");
        $this->assertEquals($instant, $clock->now());

        # Move time forward and backward through the source clock
        $clock->adjustTime("+2 hours");
        $this->assertEquals($instant->modify("+2 hours"), $clock->now());
        $clock->adjustTime("-30 minutes");
        $this->assertEquals($instant->modify("+90 minutes"), $clock->now());

        # Replace time with a CrazyPHP date in another timezone
        $replacement = new DateTime("2027-01-01 00:00:00.654321 Asia/Tokyo");
        $clock->setTo($replacement);
        $this->assertEquals($replacement, $clock->now());
        $this->assertSame("Asia/Tokyo", $clock->now()->getTimezone()->getName());
        $this->assertEquals($instant, $original);

    }

    /**
     * Test Invalid Adjustment Preserves Time
     *
     * @return void
     */
    public function testInvalidAdjustmentPreservesTime():void {

        # Prepare a fixed clock before an invalid adjustment
        $instant = new DateTime("2026-01-01 00:00:00 UTC");
        $clock = new FrozenClock($instant);
        $this->expectException(DateMalformedStringException::class);

        try{

            # Forward invalid modifiers to the upstream implementation
            $clock->adjustTime("not-a-date-modifier");

        }finally{

            # Assert failed adjustments leave the frozen instant unchanged
            $this->assertEquals($instant, $clock->now());

        }

    }

    /**
     * Test Frozen Timezone Factories
     *
     * @return void
     */
    public function testFrozenTimezoneFactories():void {

        # Preserve global timezone state
        $timezone = date_default_timezone_get();

        try{

            # Freeze the current time with both timezone factories
            date_default_timezone_set("Asia/Tokyo");
            $before = new DateTimeImmutable("now");
            $system = FrozenClock::fromSystemTimezone();
            $utc = FrozenClock::fromUTC();
            $after = new DateTimeImmutable("now");

            # Assert the factory timezones and captured instants
            $this->assertSame("Asia/Tokyo", $system->now()->getTimezone()->getName());
            $this->assertSame("UTC", $utc->now()->getTimezone()->getName());
            foreach([$system, $utc] as $clock){

                # Assert each factory freezes a current CrazyPHP date
                $result = $clock->now();
                $this->assertInstanceOf(DateTime::class, $result);
                $this->assertGreaterThanOrEqual($before, $result);
                $this->assertLessThanOrEqual($after, $result);
                $this->assertEquals($result, $clock->now());

            }

        }finally{

            # Restore the original timezone
            date_default_timezone_set($timezone);

        }

    }

}
