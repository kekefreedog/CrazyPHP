<?php declare(strict_types=1);
/**
 * Frozen Clock
 *
 * Control the lcobucci frozen clock using immutable CrazyPHP dates.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Time;

/**
 * Dependencies
 */
use Lcobucci\Clock\FrozenClock as LcobucciFrozenClock;
use DateMalformedStringException;
use DateTimeImmutable;

/**
 * Frozen Clock
 *
 * Keep time fixed until explicitly set or adjusted.
 */
class FrozenClock extends Clock {

    /** Private Parameters
     ******************************************************
     */

    /** @var LcobucciFrozenClock $_frozenClock Controlled source of time */
    private LcobucciFrozenClock $_frozenClock;

    /** Public Methods | Constructor
     ******************************************************
     */

    /**
     * Constructor
     *
     * @param DateTimeImmutable $now Initial frozen time
     */
    public function __construct(DateTimeImmutable $now){

        # Share the controlled clock with the date adapter
        $this->_frozenClock = new LcobucciFrozenClock($now);
        parent::__construct($this->_frozenClock);

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * From UTC
     *
     * @return static
     */
    public static function fromUTC():static {

        # Freeze the current system time in UTC
        return new static(LcobucciFrozenClock::fromUTC()->now());

    }

    /**
     * From System Timezone
     *
     * @return static
     */
    public static function fromSystemTimezone():static {

        # Freeze the current time in PHP's default timezone
        return new static(LcobucciFrozenClock::fromSystemTimezone()->now());

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Set To
     *
     * @param DateTimeImmutable $now New frozen time
     * @return void
     */
    public function setTo(DateTimeImmutable $now):void {

        # Replace the source clock's frozen time
        $this->_frozenClock->setTo($now);

    }

    /**
     * Adjust Time
     *
     * @param non-empty-string $modifier Relative date expression
     * @return void
     * @throws DateMalformedStringException When the modifier is invalid
     */
    public function adjustTime(string $modifier):void {

        # Apply the relative adjustment through the source clock
        $this->_frozenClock->adjustTime($modifier);

    }

}
