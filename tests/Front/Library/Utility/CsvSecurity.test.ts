/**
 * CSV Spreadsheet Protection Regression
 *
 * Verify formula-neutralized exports and lossless CSV field boundaries.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependencies
 */
import Csv from "../../../../src/Front/Library/Utility/Csv";
import assert from "node:assert/strict";
import {it} from "node:test";

// Keep ordinary machine-oriented exports backward compatible
it("preserves the existing default while protecting explicit spreadsheet exports", () => {

    // Retain a formula-shaped value for both modes
    const records = [{note: "=1+1"}];

    // Keep the preexisting plain CSV contract
    assert.equal(Csv.render(records), "note\n=1+1");

    // Prefix spreadsheet text inside an enclosing CSV field
    assert.equal(Csv.render(records, {escapeFormulas: true}), '"note"\n"\'=1+1"');

    // Keep empty exports empty
    assert.equal(Csv.render([], {escapeFormulas: true}), "");

});

// Exercise direct formulas and prefixes that spreadsheet imports can ignore
it("neutralizes formula markers and control-prefixed text", () => {

    // Include quotes, delimiters, full-width markers, whitespace, BOM and invisible controls
    const values = [
        "=1+1", "+SUM(1,2)", "-1+1", "@SUM(1,2)",
        '=HYPERLINK("https://example.invalid/","label")',
        "  =1+1", "\t=1+1", "\r=1+1", "\n=1+1", "\r\n=1+1",
        "\u0000=1+1", "\u001f =1+1", "\u007f=1+1", "\u0085=1+1",
        "\ufeff=1+1", "\u00a0+1", "\u200b=1+1", "\u2060=1+1",
        "＝1+1", "＋1", "－1", "＠SUM(1,2)",
        "\tplain text", "\rplain text", "\nplain text",
    ];

    // Check each attack independently so every marker remains covered
    for(const value of values){

        // Require a literal text marker before every original character
        assert.equal(Csv.render([{note: value}], {escapeFormulas: true}), '"note"\n"\'' + value.replace(/"/g, '""') + '"');

    }

});

// Protect column names as well as their contents
it("escapes untrusted headers and prevents delimiter or newline breakout", () => {

    // Include formulas in the header and after structural CSV characters inside a value
    const records = [{'=SUM(1,2)\r\n"header"': 'normal,"=1+1";@SUM(1,2)\rnext\nrow'}];

    // Require a single header and value field, with every internal quote doubled
    assert.equal(
        Csv.render(records, {escapeFormulas: true}),
        '"\'=SUM(1,2)\r\n""header"""\n"normal,""=1+1"";@SUM(1,2)\rnext\nrow"'
    );

    // Quote a lone carriage return even in ordinary CSV mode
    assert.equal(Csv.render([{note: "first\rsecond"}]), 'note\n"first\rsecond"');

    // Escape punctuation in headers in ordinary CSV mode too
    assert.equal(Csv.render([{'a,"b"': 1}]), '"a,""b"""\n1');

});

// Preserve ordinary report content and native numeric values
it("keeps numbers, dates, Unicode and missing values without altering source rows", () => {

    // Freeze source data to detect in-place sanitization
    const records = Object.freeze([Object.freeze({
        number: -12.5,
        zero: 0,
        decimal: 0.25,
        textNumber: "-12.5",
        date: "2026-11-15",
        label: "Équipe 東京",
        note: 'First, "quoted"\r\nSecond line',
        empty: "",
        missing: null,
        absent: undefined,
        flag: false,
    })]);

    // Serialize the frozen records without modifying them
    const csv = Csv.render([...records], {escapeFormulas: true});

    // Preserve exact representations and distinguish numeric data from untrusted text
    assert.equal(csv.split("\n")[1], '"-12.5","0","0.25","\'-12.5","2026-11-15","Équipe 東京","First, ""quoted""\r');

    // Keep missing values empty and booleans literal
    assert.ok(csv.endsWith('Second line","","","","false"'));

    // Keep the source numeric-looking text unchanged
    assert.equal(records[0].textNumber, "-12.5");

});

// Check textual representations rather than only primitive string inputs
it("protects coerced values and does not add duplicate text markers", () => {

    // Include objects, arrays and an already neutralized string
    const records = [{object: {toString: () => "=1+1"}, array: ["+1"], escaped: "'=1+1"}];

    // Keep every representation inert without adding another apostrophe to protected text
    assert.equal(Csv.render(records, {escapeFormulas: true}), '"object","array","escaped"\n"\'=1+1","\'+1","\'=1+1"');

});
