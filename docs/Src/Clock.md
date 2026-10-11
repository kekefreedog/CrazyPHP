# Clock

CrazyPHP clocks use [`lcobucci/clock`](https://github.com/lcobucci/clock) and implement both
`Lcobucci\Clock\Clock` and the [PSR-20](https://www.php-fig.org/psr/psr-20/) `Psr\Clock\ClockInterface`.
Every `now()` call returns an immutable `CrazyPHP\Library\Time\DateTime`, preserving the source instant, microseconds and timezone.
The lcobucci implementations are final classes, so CrazyPHP wraps them through composition.

The Composer dependency is `lcobucci/clock:^3.5`; version 3.5 supports PHP 8.3.
Composer selects a compatible release for the application's PHP runtime.

## Default clock

`new Clock()` uses the lcobucci system clock and captures PHP's default timezone at construction.

```php
use CrazyPHP\Library\Time\Clock;

$clock = new Clock();
$now = $clock->now();
$timestamp = $now->getTimestamp();
```

Services that need the current time should accept `Psr\Clock\ClockInterface` through their constructor.
Applications can supply `SystemClock` in production and `FrozenClock` in tests.

## System clock

```php
use CrazyPHP\Library\Time\SystemClock;

$clock = new SystemClock(new \DateTimeZone("Europe/Paris"));
$utc = SystemClock::fromUTC();
$local = SystemClock::fromSystemTimezone();
$now = $clock->now();
```

Each reading uses the current system time. `new SystemClock()` also defaults to PHP's timezone at construction.
Changing PHP's default timezone later does not change an existing clock's timezone.

## Frozen clock

```php
use CrazyPHP\Library\Time\FrozenClock;
use CrazyPHP\Library\Time\DateTime;

$clock = new FrozenClock(new DateTime("2026-10-11 12:00:00 UTC"));
$clock->adjustTime("+15 minutes");
$now = $clock->now(); // 2026-10-11 12:15:00 UTC
$clock->setTo(new DateTime("2026-10-12 09:00:00 UTC"));

$utc = FrozenClock::fromUTC();
$local = FrozenClock::fromSystemTimezone();
```

Time stays fixed until `setTo()` or `adjustTime()` changes it. The factories freeze the current time once.
The constructor and `setTo()` accept native `\DateTimeImmutable` instances as well as CrazyPHP dates.
Previously returned dates remain unchanged after clock adjustments.
Invalid modifiers propagate the upstream `\DateMalformedStringException`.

## Adapt another clock

`Clock` accepts any PSR-20 clock, including an existing lcobucci instance:

```php
use CrazyPHP\Library\Time\Clock;
use Lcobucci\Clock\FrozenClock;

$source = new FrozenClock(new \DateTimeImmutable("2026-10-11 12:00:00 UTC"));
$clock = new Clock($source);
$source->adjustTime("+1 hour");
$now = $clock->now(); // CrazyPHP DateTime at 13:00 UTC
```

## Migrating existing dates

`CrazyPHP\Library\Time\DateTime` now extends `\DateTimeImmutable` instead of `\DateTime`.
This changes all CrazyPHP date instances, including dates returned by `kz()` and `lastUpdateFile()`.
Methods such as `modify()`, `add()`, `sub()`, `setDate()`, `setTime()`, `setTimestamp()` and `setTimezone()` return a new instance.
Assign the result whenever the updated date is needed:

```php
$date = $date->modify("+1 day");
$date = $date->setTime(0, 0, 0);
```

CrazyPHP dates no longer satisfy a `\DateTime` type hint or `instanceof \DateTime` check.
Consumers accepting both mutable and immutable dates can use `\DateTimeInterface`.
For an API requiring a mutable native date, convert explicitly with `\DateTime::createFromImmutable($date)`.

The existing `DateTime::getCurrent()` helper still uses `hrtime(true)` for elapsed-time measurements.
Use `$clock->now()->getTimestamp()` when a Unix timestamp is needed.
