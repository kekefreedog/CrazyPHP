/**
 * DateTime Test
 *
 * Tests for Front TS DateTime utility methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { describe, it } from "node:test";
import assert from "node:assert/strict";
import { DateTime as Luxon } from "luxon";
import DateTime from "../../../../src/Front/Library/Utility/DateTime";

/**
 * DateTime
 *
 * Methods for test DateTime methods
 */
describe("Front/Library/Utility/DateTime", () => {

    /** toUTCString
     ******************************************************
     */
    describe("toUTCString", () => {

        it("formats a date as 'YYYY-MM-DD HH:MM:SS UTC'", () => {

            // Set input (fixed date, no ambiguity since UTC getters are used)
            const date = new Date(Date.UTC(2024, 0, 5, 8, 6, 3));

            // Check result
            assert.equal(DateTime.toUTCString(date), "2024-01-05 08:06:03 UTC");

        });

        it("pads single digit month/day/hour/minute/second", () => {

            // Set input
            const date = new Date(Date.UTC(2024, 8, 9, 1, 2, 3));

            // Check result
            assert.equal(DateTime.toUTCString(date), "2024-09-09 01:02:03 UTC");

        });

        it("accepts a Luxon DateTime input", () => {

            // Set input
            const date = Luxon.fromISO("2024-01-05T08:06:03Z", { zone: "utc" });

            // Check result
            assert.equal(DateTime.toUTCString(date), "2024-01-05 08:06:03 UTC");

        });

    });

    /** toISOString
     ******************************************************
     */
    describe("toISOString", () => {

        it("formats a date as an ISO-8601 UTC string", () => {

            // Set input
            const date = new Date(Date.UTC(2024, 0, 5, 8, 6, 3, 500));

            // Check result
            assert.equal(DateTime.toISOString(date), "2024-01-05T08:06:03.500Z");

        });

        it("pads a single digit millisecond fraction", () => {

            // Set input
            const date = new Date(Date.UTC(2024, 0, 5, 8, 6, 3, 5));

            // Check result
            assert.equal(DateTime.toISOString(date), "2024-01-05T08:06:03.005Z");

        });

        it("accepts a Luxon DateTime input", () => {

            // Set input
            const date = Luxon.fromISO("2024-01-05T08:06:03.500Z", { zone: "utc" });

            // Check result
            assert.equal(DateTime.toISOString(date), "2024-01-05T08:06:03.500Z");

        });

    });

    /** toYYYYMMDDFormat
     ******************************************************
     */
    describe("toYYYYMMDDFormat", () => {

        it("formats a date with the default '/' separator", () => {

            // Set input (use noon UTC so local timezone shifts can't change the calendar day)
            const date = new Date("2024-03-07T12:00:00Z");

            // Check result
            assert.equal(DateTime.toYYYYMMDDFormat(date), "2024/03/07");

        });

        it("formats a date with a custom separator", () => {

            // Set input
            const date = new Date("2024-03-07T12:00:00Z");

            // Check result
            assert.equal(DateTime.toYYYYMMDDFormat(date, "-"), "2024-03-07");

        });

        it("accepts a Luxon DateTime input", () => {

            // Set input
            const date = Luxon.fromObject({year: 2024, month: 3, day: 7});

            // Check result
            assert.equal(DateTime.toYYYYMMDDFormat(date), "2024/03/07");

        });

    });

    /** getNextDay
     ******************************************************
     */
    describe("getNextDay", () => {

        it("throws for a weekday number below 1", () => {

            // Check result
            assert.throws(() => DateTime.getNextDay(0 as any));

        });

        it("throws for a weekday number above 7", () => {

            // Check result
            assert.throws(() => DateTime.getNextDay(8 as any));

        });

        it("returns a YYYY/MM/DD string by default", () => {

            // Set result
            const result = DateTime.getNextDay(1);

            // Check result
            assert.match(result as string, /^\d{4}\/\d{2}\/\d{2}$/);

        });

        it("returns the requested UTC weekday", () => {

            // Iteration of every accepted weekday (1 Monday .. 7 Sunday)
            for(const weekday of [1, 2, 3, 4, 5, 6, 7] as const){

                // Set result
                const result = DateTime.getNextDay(weekday, true) as Date;

                // Convert 1..7 (Monday..Sunday) convention to JS getUTCDay() 0..6 (Sunday..Saturday)
                const expectedUTCDay = weekday % 7;

                // Check result
                assert.equal(result.getUTCDay(), expectedUTCDay);

            }

        });

        it("returns a Date instance when returnDate is true", () => {

            // Check result
            assert.ok(DateTime.getNextDay(3, true) instanceof Date);

        });

    });

    /** getTodayDateYYMMDD / getTodayDateYYYYMMDD / getTodayDateYYYY_MM_DD / getTodayDate
     ******************************************************
     *
     * These methods are built on `new Date()` ("now"), so we only assert the
     * structural shape of their output rather than an exact value.
     */
    describe("getTodayDateYYMMDD", () => {

        it("returns a 6 digit YYMMDD string", () => {

            // Check result
            assert.match(DateTime.getTodayDateYYMMDD(), /^\d{6}$/);

        });

    });

    describe("getTodayDateYYYYMMDD", () => {

        it("returns an 8 digit YYYYMMDD string", () => {

            // Check result
            assert.match(DateTime.getTodayDateYYYYMMDD(), /^\d{8}$/);

        });

    });

    describe("getTodayDateYYYY_MM_DD", () => {

        it("returns a YYYY-MM-DD string", () => {

            // Check result
            assert.match(DateTime.getTodayDateYYYY_MM_DD(), /^\d{4}-\d{2}-\d{2}$/);

        });

    });

    describe("getTodayDate", () => {

        it("uses '-' as the default separator", () => {

            // Check result
            assert.match(DateTime.getTodayDate(), /^\d{4}-\d{2}-\d{2}$/);

        });

        it("uses the given separator", () => {

            // Check result
            assert.match(DateTime.getTodayDate("."), /^\d{4}\.\d{2}\.\d{2}$/);

        });

    });

    /** convertDateFormat
     ******************************************************
     */
    describe("convertDateFormat", () => {

        it("returns an empty string for a falsy input", () => {

            // Check result
            assert.equal(DateTime.convertDateFormat(""), "");

        });

        // NOTE: the docstring on DateTime.convertDateFormat says "To D/M/YYYY" (day
        // first), but the implementation actually builds
        // `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`, which is
        // M/D/YYYY (US month-first order), not D/M/YYYY. This assertion intentionally
        // pins the current (documented-wrong) behavior - see conversation with the
        // maintainer. Flip the expected value to day-first once
        // DateTime.convertDateFormat is fixed to match its docstring.
        it("actually formats as M/D/YYYY, not D/M/YYYY as documented (see note above)", () => {

            // Set input (noon UTC to dodge local timezone day-shifts)
            const result = DateTime.convertDateFormat("2024-03-07T12:00:00Z");

            // Set expected using the very same Date parsing the source relies on
            const date = new Date("2024-03-07T12:00:00Z");
            const expected = `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`;

            // Check result
            assert.equal(result, expected);

        });

    });

    /** getFirstDateOfWeek
     ******************************************************
     */
    describe("getFirstDateOfWeek", () => {

        it("returns a Monday for the current week (offset 0)", () => {

            // Set result
            const result = DateTime.getFirstDateOfWeek(0, "YYYY-MM-DD");

            // Rebuild a Date at local midnight from the formatted string to check its weekday
            const [y, m, d] = result.split("-").map(Number);
            const rebuilt = new Date(y, m - 1, d);

            // Check result (1 = Monday)
            assert.equal(rebuilt.getDay(), 1);

        });

        it("shifts by exactly 7 days per week of offset", () => {

            // Set results
            const week0 = DateTime.getFirstDateOfWeek(0, "YYYY-MM-DD");
            const week1 = DateTime.getFirstDateOfWeek(1, "YYYY-MM-DD");

            // Rebuild dates
            const toDate = (s:string) => { const [y,m,d] = s.split("-").map(Number); return new Date(y, m-1, d); };

            // Check result
            const diffDays = (toDate(week1).getTime() - toDate(week0).getTime()) / 86400000;
            assert.equal(diffDays, 7);

        });

        it("formats using the requested format", () => {

            // Check result
            assert.match(DateTime.getFirstDateOfWeek(0, "YYYYMMDD"), /^\d{8}$/);
            assert.match(DateTime.getFirstDateOfWeek(0, "MM/DD/YY"), /^\d{2}\/\d{2}\/\d{2}$/);
            assert.match(DateTime.getFirstDateOfWeek(0, "DD/MM/YYYY"), /^\d{2}\/\d{2}\/\d{4}$/);

        });

    });

    /** isDateInCurrentWeek / isDateInNextWeek
     ******************************************************
     *
     * Both methods build their "current/next week" window from `new Date()`
     * ("now"), so - to stay deterministic - we temporarily replace the global
     * `Date` with a subclass that answers a fixed instant for `new Date()` /
     * `Date.now()` while still delegating to the real `Date` for every other
     * call (parsing a date string, building an explicit y/m/d date, etc.).
     * This mirrors the jsdom before/after pattern used for `decodeHTML` in
     * Strings.test.ts, scoped to a single `it()` via try/finally instead of a
     * whole describe block, since each test needs its own fixed "now".
     */

    /**
     * Run a callback with `new Date()`/`Date.now()` pinned to `fixedNow`,
     * restoring the real `Date` constructor afterwards.
     */
    const withFixedNow = <T,>(fixedNow:Date, fn:() => T):T => {

        // Set real date
        const RealDate = Date;

        // Set mock date
        class MockDate extends RealDate {
            constructor(...args:any[]) {
                if(args.length === 0)
                    super(fixedNow.getTime());
                else
                    // @ts-ignore - forwarding constructor args
                    super(...args);
            }
            static now(){ return fixedNow.getTime(); }
        }

        // Install mock date
        (globalThis as any).Date = MockDate;

        // Try
        try {

            // Return result
            return fn();

        // Restore real date
        } finally {

            // Restore
            (globalThis as any).Date = RealDate;

        }

    };

    describe("isDateInCurrentWeek", () => {

        it("is true for today and for the start of the current week", () => {

            // Set fixed now (Wednesday 2026-09-02)
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInCurrentWeek(now), true);
                assert.equal(DateTime.isDateInCurrentWeek(new Date(2026, 7, 31)), true); // Monday, start of week

            });

        });

        it("is false for a date clearly before the current week", () => {

            // Set fixed now
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInCurrentWeek(new Date(2026, 7, 20)), false);

            });

        });

        it("is true for the end of the current week even when the week crosses a month boundary", () => {

            // Set fixed now (Wednesday 2026-09-02, week starts Monday 2026-08-31)
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result: Sunday 2026-09-06 is the real end of that week
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInCurrentWeek(new Date(2026, 8, 6)), true);

            });

        });

        it("is false for a date 3 weeks out when the week crosses a month boundary", () => {

            // Set fixed now (Wednesday 2026-09-02, week starts in August)
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInCurrentWeek(new Date(2026, 8, 20)), false);

            });

        });

        it("is false once past the end of week", () => {

            // Set fixed now
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInCurrentWeek(new Date(2026, 9, 10)), false);

            });

        });

        it("accepts a date string", () => {

            // Set fixed now (Monday, week stays within the same month)
            const now = new Date(2026, 8, 14, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInCurrentWeek("2026-09-15T12:00:00"), true);

            });

        });

        it("accepts a Luxon DateTime input", () => {

            // Set fixed now (Wednesday 2026-09-02)
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInCurrentWeek(Luxon.fromObject({year: 2026, month: 9, day: 3})), true);

            });

        });

    });

    describe("isDateInNextWeek", () => {

        it("is false for today", () => {

            // Set fixed now
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInNextWeek(now), false);

            });

        });

        it("is true for a date in next week when next week doesn't cross a month boundary", () => {

            // Set fixed now (Wednesday 2026-09-02; next week is 2026-09-07 to 2026-09-13, same month)
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInNextWeek(new Date(2026, 8, 10)), true);
                assert.equal(DateTime.isDateInNextWeek(new Date(2026, 8, 20)), false);

            });

        });

        // NOTE: just like `isDateInCurrentWeek`, `isDateInNextWeek` computes
        // `endOfNextWeek` by cloning `today` and calling
        // `endOfNextWeek.setDate(startOfNextWeek.getDate() + 6)`, losing the
        // month/year of `startOfNextWeek`. Whenever next week starts in a
        // different month than "today" (i.e. whenever next week crosses a
        // month boundary, which happens roughly once a month), the resulting
        // `endOfNextWeek` is computed against "today"'s month instead of next
        // week's month and ends up *before* `startOfNextWeek` - making the
        // range empty, so the method incorrectly returns false for every date,
        // including dates that are genuinely in next week. With "now" pinned
        // to Monday 2026-09-28 (next week is 2026-10-05 to 2026-10-11, in
        // October), a clearly-next-week date like 2026-10-08 is reported as
        // NOT being in next week. This assertion intentionally pins that
        // current (buggy) behavior - see conversation with the maintainer.
        // Update it once DateTime.isDateInNextWeek computes `endOfNextWeek`
        // from a clone of `startOfNextWeek` instead of a clone of `today`.
        it("incorrectly reports a genuine next-week date as not being in next week when next week crosses a month boundary (see note above)", () => {

            // Set fixed now (Monday 2026-09-28, next week starts in October)
            const now = new Date(2026, 8, 28, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInNextWeek(new Date(2026, 9, 8)), false);

            });

        });

        it("accepts a date string", () => {

            // Set fixed now
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(typeof DateTime.isDateInNextWeek("2026-09-10T12:00:00"), "boolean");

            });

        });

        it("accepts a Luxon DateTime input", () => {

            // Set fixed now (Wednesday 2026-09-02; next week is 2026-09-07 to 2026-09-13, same month)
            const now = new Date(2026, 8, 2, 10, 0, 0);

            // Check result
            withFixedNow(now, () => {

                assert.equal(DateTime.isDateInNextWeek(Luxon.fromObject({year: 2026, month: 9, day: 10})), true);

            });

        });

    });

    /** isValidDate
     ******************************************************
     */
    describe("isValidDate", () => {

        it("returns true for a parseable date string", () => {

            // Check result
            assert.equal(DateTime.isValidDate("2024-01-05"), true);

        });

        it("returns false for an empty string", () => {

            // Check result
            assert.equal(DateTime.isValidDate(""), false);

        });

        it("returns false for gibberish", () => {

            // Check result
            assert.equal(DateTime.isValidDate("not-a-date"), false);

        });

    });

    /** formatDate
     ******************************************************
     */
    describe("formatDate", () => {

        // Set fixed reference date (noon UTC to dodge local timezone day-shifts)
        const date = new Date("2024-03-07T12:00:00Z");

        it("formats as YYYY-MM-DD", () => {

            // Check result
            assert.equal(DateTime.formatDate(date, "YYYY-MM-DD"), "2024-03-07");

        });

        it("formats as YYYY/MM/DD", () => {

            // Check result
            assert.equal(DateTime.formatDate(date, "YYYY/MM/DD"), "2024/03/07");

        });

        it("formats as YYYYMMDD", () => {

            // Check result
            assert.equal(DateTime.formatDate(date, "YYYYMMDD"), "20240307");

        });

        it("formats as MM/DD/YY", () => {

            // Check result
            assert.equal(DateTime.formatDate(date, "MM/DD/YY"), "03/07/24");

        });

        it("formats as DD/MM/YYYY", () => {

            // Check result
            assert.equal(DateTime.formatDate(date, "DD/MM/YYYY"), "07/03/2024");

        });

        it("accepts a date string as input", () => {

            // Check result
            assert.equal(DateTime.formatDate("2024-03-07T12:00:00Z", "YYYY-MM-DD"), "2024-03-07");

        });

        it("accepts a Luxon DateTime input", () => {

            // Set input
            const luxonDate = Luxon.fromObject({year: 2024, month: 3, day: 7});

            // Check result
            assert.equal(DateTime.formatDate(luxonDate, "YYYY-MM-DD"), "2024-03-07");

        });

        it("throws for an unsupported format", () => {

            // Check result
            assert.throws(() => DateTime.formatDate(date, "nope" as any));

        });

    });

    /** getAllDaysBetween
     ******************************************************
     */
    describe("getAllDaysBetween", () => {

        it("returns every day between two dates, inclusive", () => {

            // Set input
            const start = new Date(2024, 2, 1);
            const end = new Date(2024, 2, 3);

            // Set result
            const result = DateTime.getAllDaysBetween(start, end);

            // Check result
            assert.equal(result.length, 3);
            assert.equal(result[0].getDate(), 1);
            assert.equal(result[1].getDate(), 2);
            assert.equal(result[2].getDate(), 3);

        });

        it("returns a single day when start equals end", () => {

            // Set input
            const day = new Date(2024, 2, 1);

            // Check result
            assert.equal(DateTime.getAllDaysBetween(day, new Date(day)).length, 1);

        });

        it("returns an empty array when start is after end", () => {

            // Set input
            const start = new Date(2024, 2, 10);
            const end = new Date(2024, 2, 1);

            // Check result
            assert.deepEqual(DateTime.getAllDaysBetween(start, end), []);

        });

        it("accepts Luxon DateTime inputs (both, or mixed with a native Date)", () => {

            // Set input
            const start = Luxon.fromObject({year: 2024, month: 3, day: 1});
            const end = Luxon.fromObject({year: 2024, month: 3, day: 3});

            // Check result | both Luxon
            const result = DateTime.getAllDaysBetween(start, end);
            assert.equal(result.length, 3);
            assert.equal(result[0].getDate(), 1);
            assert.equal(result[1].getDate(), 2);
            assert.equal(result[2].getDate(), 3);

            // Check result | mixed Luxon / native Date
            const mixed = DateTime.getAllDaysBetween(start, new Date(2024, 2, 3));
            assert.equal(mixed.length, 3);

        });

    });

    /** isWeekend
     ******************************************************
     */
    describe("isWeekend", () => {

        it("returns true for a Saturday", () => {

            // Set input (2024-03-09 is a Saturday)
            const date = new Date(2024, 2, 9);

            // Check result
            assert.equal(date.getDay(), 6);
            assert.equal(DateTime.isWeekend(date), true);

        });

        it("returns true for a Sunday", () => {

            // Set input (2024-03-10 is a Sunday)
            const date = new Date(2024, 2, 10);

            // Check result
            assert.equal(date.getDay(), 0);
            assert.equal(DateTime.isWeekend(date), true);

        });

        it("returns false for a weekday", () => {

            // Set input (2024-03-06 is a Wednesday)
            const date = new Date(2024, 2, 6);

            // Check result
            assert.equal(DateTime.isWeekend(date), false);

        });

        it("accepts a Luxon DateTime input", () => {

            // Set input (2024-03-09 is a Saturday)
            const date = Luxon.fromObject({year: 2024, month: 3, day: 9});

            // Check result
            assert.equal(DateTime.isWeekend(date), true);

        });

    });

    /** mergeDate
     ******************************************************
     */
    describe("mergeDate", () => {

        it("merges numeric year/month/day into YYYY-MM-DD", () => {

            // Check result
            assert.equal(DateTime.mergeDate(2024, 3, 7), "2024-03-07");

        });

        it("merges string year/month/day into YYYY-MM-DD", () => {

            // Check result
            assert.equal(DateTime.mergeDate("2024", "3", "7"), "2024-03-07");

        });

        it("throws for an invalid date (Feb 30th)", () => {

            // Check result
            assert.throws(() => DateTime.mergeDate(2024, 2, 30));

        });

    });

    /** explodeDate
     ******************************************************
     */
    describe("explodeDate", () => {

        it("splits a YYYY-MM-DD string into [year, month, day]", () => {

            // Check result
            assert.deepEqual(DateTime.explodeDate("2024-03-07"), [2024, 3, 7]);

        });

        it("throws when the string doesn't have exactly 3 hyphen separated parts", () => {

            // Check result
            assert.throws(() => DateTime.explodeDate("2024/03/07"));

        });

    });

    /** toLocalFormat
     ******************************************************
     */
    describe("toLocalFormat", () => {

        it("formats a date in English", () => {

            // Set input (noon UTC to dodge local timezone day-shifts)
            const input = "2024-03-07T12:00:00Z";
            const date = new Date(input);

            // Set expected using the same weekday/month names the source relies on
            const weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            const expected = `${weekdays[date.getDay()]} ${date.getDate()} ${months[date.getMonth()]}`;

            // Check result
            assert.equal(DateTime.toLocalFormat(input, "en_US"), expected);

        });

        it("formats a date in French", () => {

            // Set input
            const input = "2024-03-07T12:00:00Z";
            const date = new Date(input);

            // Set expected
            const weekdays = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
            const months = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
            const expected = `${weekdays[date.getDay()]} ${date.getDate()} ${months[date.getMonth()]}`;

            // Check result
            assert.equal(DateTime.toLocalFormat(input, "fr_FR"), expected);

        });

        it("falls back to en_US for an unsupported locale", () => {

            // Set input
            const input = "2024-03-07T12:00:00Z";

            // Check result
            assert.equal(DateTime.toLocalFormat(input, "xx_XX"), DateTime.toLocalFormat(input, "en_US"));

        });

        it("returns the input untouched when input is falsy", () => {

            // Check result
            assert.equal(DateTime.toLocalFormat("", "en_US"), "");

        });

        it("returns the input untouched when locale is falsy", () => {

            // Check result
            assert.equal(DateTime.toLocalFormat("2024-03-07T12:00:00Z", ""), "2024-03-07T12:00:00Z");

        });

    });

    /** isBeforeToday
     ******************************************************
     */
    describe("isBeforeToday", () => {

        it("returns true for yesterday", () => {

            // Set input
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);

            // Check result
            assert.equal(DateTime.isBeforeToday(yesterday), true);

        });

        it("returns false for today", () => {

            // Check result
            assert.equal(DateTime.isBeforeToday(new Date()), false);

        });

        it("returns false for tomorrow", () => {

            // Set input
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);

            // Check result
            assert.equal(DateTime.isBeforeToday(tomorrow), false);

        });

        it("accepts a Luxon DateTime input", () => {

            // Set input
            const yesterday = Luxon.now().minus({days: 1});

            // Check result
            assert.equal(DateTime.isBeforeToday(yesterday), true);

        });

    });

    /** normalize
     ******************************************************
     */
    describe("normalize", () => {

        it("keeps the order when from is already before to", () => {

            // Set input
            const from = Luxon.fromISO("2024-01-01T00:00:00Z", { zone: "utc" });
            const to = Luxon.fromISO("2024-01-10T00:00:00Z", { zone: "utc" });

            // Set result
            const result = DateTime.normalize(from, to);

            // Check result
            assert.equal(result.start.toISO(), from.toISO());
            assert.equal(result.end.toISO(), to.toISO());

        });

        it("swaps from and to when from is after to", () => {

            // Set input
            const from = Luxon.fromISO("2024-01-10T00:00:00Z", { zone: "utc" });
            const to = Luxon.fromISO("2024-01-01T00:00:00Z", { zone: "utc" });

            // Set result
            const result = DateTime.normalize(from, to);

            // Check result
            assert.equal(result.start.toISO(), to.toISO());
            assert.equal(result.end.toISO(), from.toISO());

        });

        it("accepts plain JS Date instances", () => {

            // Set input
            const from = new Date(Date.UTC(2024, 0, 1));
            const to = new Date(Date.UTC(2024, 0, 10));

            // Set result
            const result = DateTime.normalize(from, to);

            // Check result
            assert.equal(result.start.toMillis(), from.getTime());
            assert.equal(result.end.toMillis(), to.getTime());

        });

    });

    /** offsetRange
     ******************************************************
     */
    describe("offsetRange", () => {

        // Set fixed 10 day range
        const from = Luxon.fromISO("2024-01-01T00:00:00Z", { zone: "utc" });
        const to = Luxon.fromISO("2024-01-11T00:00:00Z", { zone: "utc" });

        it("shifts the range towards the future by default", () => {

            // Set result (default direction 'future', default ratio 0.5 => shift by 5 days)
            const result = DateTime.offsetRange(from, to);

            // Check result
            assert.equal(result.from.toISODate(), "2024-01-06");
            assert.equal(result.to.toISODate(), "2024-01-16");

        });

        it("shifts the range towards the past when asked", () => {

            // Set result
            const result = DateTime.offsetRange(from, to, "past");

            // Check result
            assert.equal(result.from.toISODate(), "2023-12-27");
            assert.equal(result.to.toISODate(), "2024-01-06");

        });

        it("uses the given ratio", () => {

            // Set result (10 days * ratio 0.1 = 1 day shift)
            const result = DateTime.offsetRange(from, to, "future", 0.1);

            // Check result
            assert.equal(result.from.toISODate(), "2024-01-02");
            assert.equal(result.to.toISODate(), "2024-01-12");

        });

    });

    /** alignRange
     ******************************************************
     */
    describe("alignRange", () => {

        it("centers the range on the target date", () => {

            // Set input (10 day range, half = 5 days)
            const from = Luxon.fromISO("2024-01-01T00:00:00Z", { zone: "utc" });
            const to = Luxon.fromISO("2024-01-11T00:00:00Z", { zone: "utc" });
            const target = Luxon.fromISO("2024-06-15T00:00:00Z", { zone: "utc" });

            // Set result
            const result = DateTime.alignRange(from, to, target);

            // Check result
            assert.equal(result.from.toISODate(), "2024-06-10");
            assert.equal(result.to.toISODate(), "2024-06-20");

        });

        it("accepts a plain JS Date as target", () => {

            // Set input
            const from = Luxon.fromISO("2024-01-01T00:00:00Z", { zone: "utc" });
            const to = Luxon.fromISO("2024-01-11T00:00:00Z", { zone: "utc" });
            const target = new Date(Date.UTC(2024, 5, 15));

            // Set result
            const result = DateTime.alignRange(from, to, target);

            // Check result
            assert.equal(result.from.toISODate(), "2024-06-10");
            assert.equal(result.to.toISODate(), "2024-06-20");

        });

    });

    /** alignRangeByRatio
     ******************************************************
     */
    describe("alignRangeByRatio", () => {

        // Set input range spanning a full year
        const from = Luxon.fromISO("2024-01-01T00:00:00Z", { zone: "utc" });
        const to = Luxon.fromISO("2024-12-31T23:59:59Z", { zone: "utc" });

        it("aligns to the ISO week containing the ratio's pivot date", () => {

            // Set result (ratio 0 => pivot = 'from' = 2024-01-01, a Monday)
            const result = DateTime.alignRangeByRatio(from, to, "week", 0);

            // Check result
            assert.equal(result.from.weekday, 1);
            assert.equal(result.to.weekday, 7);

        });

        it("aligns to the month containing the ratio's pivot date", () => {

            // Set result
            const result = DateTime.alignRangeByRatio(from, to, "month", 0);

            // Check result
            assert.equal(result.from.toISODate(), "2024-01-01");
            assert.equal(result.from.day, 1);
            assert.equal(result.to.month, 1);

        });

        it("aligns to the semester containing the ratio's pivot date", () => {

            // Set result (ratio 0 => pivot is in January => Jan-Jun semester)
            const result = DateTime.alignRangeByRatio(from, to, "semester", 0);

            // Check result
            assert.equal(result.from.toISODate(), "2024-01-01");
            assert.equal(result.from.month, 1);

        });

        it("aligns to the semester after mid-year", () => {

            // Set result (ratio 0.6 lands the pivot around end of July => Jul-Dec semester)
            const result = DateTime.alignRangeByRatio(from, to, "semester", 0.6);

            // Check result
            assert.equal(result.from.month, 7);
            assert.equal(result.from.day, 1);

        });

        it("aligns to the year containing the ratio's pivot date", () => {

            // Set result
            const result = DateTime.alignRangeByRatio(from, to, "year", 0.5);

            // Check result
            assert.equal(result.from.toISODate(), "2024-01-01");
            assert.equal(result.to.toISODate(), "2024-12-31");

        });

        it("clamps a ratio below 0 to 0", () => {

            // Set results
            const clamped = DateTime.alignRangeByRatio(from, to, "year", -1);
            const zero = DateTime.alignRangeByRatio(from, to, "year", 0);

            // Check result
            assert.equal(clamped.from.toISODate(), zero.from.toISODate());

        });

        it("clamps a ratio above 1 to 1", () => {

            // Set results
            const clamped = DateTime.alignRangeByRatio(from, to, "year", 2);
            const one = DateTime.alignRangeByRatio(from, to, "year", 1);

            // Check result
            assert.equal(clamped.from.toISODate(), one.from.toISODate());

        });

        it("defaults the ratio to 0.5", () => {

            // Set results
            const withDefault = DateTime.alignRangeByRatio(from, to, "year");
            const withHalf = DateTime.alignRangeByRatio(from, to, "year", 0.5);

            // Check result
            assert.equal(withDefault.from.toISODate(), withHalf.from.toISODate());

        });

    });

});
