<?php declare(strict_types=1);
/**
 * System Clock
 *
 * Read the lcobucci system clock as immutable CrazyPHP dates.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Time;

/**
 * Dependencies
 */
use Lcobucci\Clock\SystemClock as LcobucciSystemClock;
use DateTimeZone;

/**
 * System Clock
 *
 * Provide the current time in a selected timezone.
 */
class SystemClock extends Clock {

    /** Public Methods | Constructor
     ******************************************************
     */

    /**
     * Constructor
     *
     * @param DateTimeZone|null $timezone Timezone, defaulting to PHP's system timezone
     */
    public function __construct(?DateTimeZone $timezone = null){

        # Delegate time readings to the lcobucci system clock
        parent::__construct(new LcobucciSystemClock($timezone ?? new DateTimeZone(date_default_timezone_get())));

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

        # Create a system clock in UTC
        return new static(new DateTimeZone("UTC"));

    }

    /**
     * From System Timezone
     *
     * @return static
     */
    public static function fromSystemTimezone():static {

        # Capture PHP's current default timezone
        return new static(new DateTimeZone(date_default_timezone_get()));

    }

}
