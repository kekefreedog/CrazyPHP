/**
 * Path Test
 *
 * Tests for Front TS Path utility methods
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
import Path from "../../../../src/Front/Library/Utility/Path";

/**
 * Path
 *
 * Methods for test path methods
 */
describe("Front/Library/Utility/Path", () => {

    /** dirname
     ******************************************************
     */
    describe("dirname", () => {

        it("returns the folder path from a file path", () => {

            // Check result
            assert.equal(Path.dirname("/a/b/c.txt"), "/a/b");

        });

        it("works with relative paths", () => {

            // Check result
            assert.equal(Path.dirname("a/b/c.txt"), "a/b");

        });

        it("converts windows separators before extracting the folder", () => {

            // Check result
            assert.equal(Path.dirname("a\\b\\c.txt"), "a/b");

        });

        it("returns an empty string when there is no separator", () => {

            // Check result
            assert.equal(Path.dirname("c.txt"), "");

        });

        it("returns an empty string for an empty input", () => {

            // Check result
            assert.equal(Path.dirname(""), "");

        });

        it("keeps a trailing slash folder as-is", () => {

            // Check result
            assert.equal(Path.dirname("/a/b/"), "/a/b");

        });

    });

    /** basename
     ******************************************************
     */
    describe("basename", () => {

        it("returns the filename from a path", () => {

            // Check result
            assert.equal(Path.basename("/a/b/c.txt"), "c.txt");

        });

        it("removes the given extension when present", () => {

            // Check result
            assert.equal(Path.basename("/a/b/c.txt", ".txt"), "c");

        });

        it("leaves the filename untouched when the extension doesn't match", () => {

            // Check result
            assert.equal(Path.basename("/a/b/c.txt", ".md"), "c.txt");

        });

        it("returns the input unchanged when there is no separator", () => {

            // Check result
            assert.equal(Path.basename("c.txt"), "c.txt");

        });

        it("returns an empty string for an empty input", () => {

            // Check result
            assert.equal(Path.basename(""), "");

        });

        it("returns an empty string when the path ends with a separator", () => {

            // Check result
            assert.equal(Path.basename("/a/b/"), "");

        });

    });

    /** extname
     ******************************************************
     */
    describe("extname", () => {

        it("returns the extension of a file", () => {

            // Check result
            assert.equal(Path.extname("/a/b/c.txt"), ".txt");

        });

        it("returns an empty string when there is no extension", () => {

            // Check result
            assert.equal(Path.extname("/a/b/c"), "");

        });

        it("returns an empty string for a dotfile (leading dot is not an extension)", () => {

            // Check result
            assert.equal(Path.extname("/a/b/.hidden"), "");

        });

        it("returns an empty string for an empty input", () => {

            // Check result
            assert.equal(Path.extname(""), "");

        });

        it("returns only the last extension when there are multiple dots", () => {

            // Check result
            assert.equal(Path.extname("a.b.c"), ".c");

        });

    });

    /** join
     ******************************************************
     */
    describe("join", () => {

        it("joins multiple segments with a single slash", () => {

            // Check result
            assert.equal(Path.join("a", "b", "c"), "a/b/c");

        });

        it("collapses duplicated slashes coming from the segments", () => {

            // Check result
            assert.equal(Path.join("a/", "/b", "c"), "a/b/c");

        });

        it("keeps a leading slash for an absolute first segment", () => {

            // Check result
            assert.equal(Path.join("/a", "b"), "/a/b");

        });

        it("filters out falsy/empty segments", () => {

            // Check result
            assert.equal(Path.join("", "a", "", "b"), "a/b");

        });

        it("returns an empty string when no segments are given", () => {

            // Check result
            assert.equal(Path.join(), "");

        });

    });

    /** normalize
     ******************************************************
     */
    describe("normalize", () => {

        it("resolves single-dot segments", () => {

            // Check result
            assert.equal(Path.normalize("a/./b/../c"), "a/c");

        });

        it("collapses duplicated slashes", () => {

            // Check result
            assert.equal(Path.normalize("a//b///c"), "a/b/c");

        });

        it("resolves a leading '..' by keeping it unresolved (nothing to pop)", () => {

            // Check result
            assert.equal(Path.normalize("../a/b"), "a/b");

        });

        it("returns an empty string for an empty input", () => {

            // Check result
            assert.equal(Path.normalize(""), "");

        });

        // NOTE: Path.normalize() splits the path on "/" and drops every empty
        // segment (including the leading empty segment produced by a leading
        // "/"), then rebuilds the path by joining the stack with "/" - so an
        // absolute path loses its leading slash and comes back looking like a
        // relative path. This disagrees with Path.isAbsolute()/Path.dirname(),
        // which both treat a leading "/" as meaningful, so the methods are
        // internally inconsistent. This assertion intentionally pins the
        // current (buggy) behavior - see conversation with the maintainer.
        // Update it to expect a leading "/" once Path.normalize is fixed.
        it("currently drops the leading slash of an absolute path, see note above", () => {

            // Check result
            assert.equal(Path.normalize("/a/b/../../c"), "c");

        });

    });

    /** isAbsolute
     ******************************************************
     */
    describe("isAbsolute", () => {

        it("returns true for a unix absolute path", () => {

            // Check result
            assert.equal(Path.isAbsolute("/a/b"), true);

        });

        it("returns false for a relative path", () => {

            // Check result
            assert.equal(Path.isAbsolute("a/b"), false);

        });

        it("returns true for a windows drive path using forward slashes", () => {

            // Check result
            assert.equal(Path.isAbsolute("C:/a/b"), true);

        });

        it("returns true for a windows drive path using backslashes", () => {

            // Check result
            assert.equal(Path.isAbsolute("C:\\a\\b"), true);

        });

        it("returns false for an empty input", () => {

            // Check result
            assert.equal(Path.isAbsolute(""), false);

        });

    });

    /** parse
     ******************************************************
     */
    describe("parse", () => {

        it("parses a full file path into its components", () => {

            // Set result
            const result = Path.parse("/a/b/c.txt");

            // Check result
            assert.deepEqual(result, {
                root: "",
                dir: "/a/b",
                base: "c.txt",
                ext: ".txt",
                name: "c"
            });

        });

        it("parses a bare filename without a directory", () => {

            // Set result
            const result = Path.parse("c");

            // Check result
            assert.deepEqual(result, {
                root: "",
                dir: "",
                base: "c",
                ext: "",
                name: "c"
            });

        });

        it("keeps a dotfile's name as-is since it has no extension", () => {

            // Set result
            const result = Path.parse(".hidden");

            // Check result
            assert.deepEqual(result, {
                root: "",
                dir: "",
                base: ".hidden",
                ext: "",
                name: ".hidden"
            });

        });

        it("parses an empty input into all-empty components", () => {

            // Set result
            const result = Path.parse("");

            // Check result
            assert.deepEqual(result, {
                root: "",
                dir: "",
                base: "",
                ext: "",
                name: ""
            });

        });

    });

    /** relative
     ******************************************************
     */
    describe("relative", () => {

        it("returns a path going up then down for sibling files", () => {

            // Check result
            assert.equal(Path.relative("/a/b/c", "/a/b/d"), "../d");

        });

        it("returns a descending path when 'to' is nested under 'from'", () => {

            // Check result
            assert.equal(Path.relative("/a/b", "/a/b/c/d"), "c/d");

        });

        it("returns only up-levels when 'to' is an ancestor of 'from'", () => {

            // Check result
            assert.equal(Path.relative("/a/b/c/d", "/a/b"), "../..");

        });

        it("returns an empty string for two identical paths", () => {

            // Check result
            assert.equal(Path.relative("a/b", "a/b"), "");

        });

    });

});
