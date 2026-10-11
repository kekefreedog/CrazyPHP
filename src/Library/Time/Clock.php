<?php declare(strict_types=1);
/**
 * Time
 *
 * Adapt PSR-20 clocks to immutable CrazyPHP dates.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Time;

/**
 * Dependencies
 */
use Lcobucci\Clock\Clock as LcobucciClock;
use Lcobucci\Clock\SystemClock;
use Psr\Clock\ClockInterface;

/**
 * Clock
 *
 * Wrap a clock while preserving the CrazyPHP date return type and PSR-20 compatibility.
 */
class Clock implements LcobucciClock {

    /** Private Parameters
     ******************************************************
     */

    /** @var ClockInterface $_clock Source of the current time */
    private ClockInterface $_clock;

    /** Public Methods | Constructor
     ******************************************************
     */

    /**
     * Constructor
     *
     * Use the system timezone when no clock is supplied.
     *
     * @param ClockInterface|null $clock Clock to adapt
     */
    public function __construct(?ClockInterface $clock = null){

        # Select the supplied clock or the local system clock
        $this->_clock = $clock ?? SystemClock::fromSystemTimezone();

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Now
     *
     * Preserve the source clock's instant, microseconds and timezone.
     *
     * @return DateTime
     */
    public function now():DateTime {

        # Convert the source reading to the immutable CrazyPHP date type
        return DateTime::createFromInterface($this->_clock->now());

    }

}
