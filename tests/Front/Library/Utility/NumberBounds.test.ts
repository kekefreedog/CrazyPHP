/**
 * Number Bounds Templates Test
 *
 * Verify numeric bounds configuration and browser template rendering.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependencies
 */
import {createRequire} from "node:module";
import assert from "node:assert/strict";
import {describe, it} from "node:test";
import {readFileSync} from "node:fs";
import Handlebars from "handlebars";

// Load the shared Handlebars normalization helper
const require = createRequire(import.meta.url);
const normalize = require("../../../../resources/Js/Handlebars/numberFilterBounds.js");

/** Tests | Number bounds configuration and browser templates
 ******************************************************
 */

describe("Number bounds configuration and browser templates", () => {

    it("accepts mixed formats, nulls, labels and legacy aliases", () => {

        // Assert shorthand and legacy configuration normalization
        assert.equal(normalize({}).enabled, false);
        assert.equal(normalize({decimal: 2}).enabled, false);
        assert.equal(normalize({min: 0}).value, ">=0");
        assert.equal(normalize({max: 50}).value, "<=50");
        assert.equal(normalize({min: -2.5, max: {value: 50}}).value, "=[-2.5:50]");

        // Normalize explicit empty bounds alongside legacy aliases
        const empty = normalize({min: null, start: 10, max: {label: "At most"}, end: 50});

        // Assert explicit empty bounds override legacy endpoints
        assert.equal(empty.enabled, true);
        assert.equal(empty.value, "");
        assert.equal(empty.max.label, "At most");
        assert.equal(normalize({start: 10, end: 50}).value, "=[10:50]");
        assert.equal(normalize({minLabel: "From", maxLabel: "To"}).min.label, "From");

    });

    it("renders bounds and preserves ordinary number filters", () => {

        // Prepare the test fixture
        const handlebars = Handlebars.create();

        // Register helpers and partials used by the number filter
        handlebars.registerHelper("numberFilterBounds", normalize);
        handlebars.registerHelper("isnt", require("../../../../resources/Js/Handlebars/isnt.js"));
        for(const name of ["filter/filter_number", "filter/_number_control", "filter/_number_endpoint", "filter/_operator", "form/form_number"])
            handlebars.registerPartial(name, readFileSync(new URL(`../../../../resources/Hbs/Partials/${name}.hbs`, import.meta.url), "utf8"));

        // Render the configured bounds
        const render = handlebars.compile("{{> filter/filter_number}}");
        const base = {name: "amount", label: "Amount", form: {id: "numbers"}};
        const output = render({...base, _style: {number: {min: 0, max: {label: "At most", value: 50}}}});

        // Assert paired controls render one encoded filter value
        assert.match(output, /value="&#x3D;\[0:50\]"/);
        assert.match(output, /data-number-bound="min"/);
        assert.match(output, /data-number-bound="max"/);
        assert.match(output, />At most<\/label>/);
        assert.doesNotMatch(output, /class="filter-operator /);
        assert.equal(output.match(/name="amount"/g)?.length, 1);

        // Render the ordinary single-value filter
        const ordinary = render({...base, default: 5});

        // Assert the ordinary number filter retains its operator
        assert.doesNotMatch(ordinary, /data-filter-number-bounds/);
        assert.match(ordinary, /value="5"/);
        assert.match(ordinary, /data-operator-name="amount"/);

        // Assert custom labels are escaped
        assert.match(render({...base, _style: {number: {max: {label: '<script>alert("x")</script>'}}}}), /&lt;script&gt;/);

    });

});
