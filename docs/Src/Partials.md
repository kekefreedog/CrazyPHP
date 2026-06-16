# Partials

A Partial is a reusable UI component made of three co-located files: a Handlebars template (`.hbs`), a TypeScript class (`.ts`), and a SCSS stylesheet (`.scss`). The PHP backend renders the HTML server-side; the TypeScript class runs client-side to attach interactivity.

---

## File structure

Each partial spans three directories:

```
assets/Hbs/partials/         my_partial.hbs      ← Handlebars template
app/Environment/Partials/    MyPartial.ts         ← TypeScript class
app/Front/style/scss/partial/_my_partial.scss     ← SCSS styles
```

They are wired into the app through two index files:

```
app/Front/index.ts           ← imports TS class + registers in globalPartials map
app/Front/style/scss/index.scss ← @import for the SCSS file
```

---

## How partials are discovered (PHP)

`CrazyPHP\Library\File\Partial` is the authoritative source of truth. It discovers existing partials by scanning three sources and merging the results:

| Source | Method | What it scans |
|---|---|---|
| TypeScript | `getAllFromScript()` | `import` statements in `app/Front/index.ts` |
| SCSS | `getAllFromStyle()` | `@import` lines under the `// Partials` block in `app/Front/style/scss/index.scss` |
| Templates | `getAllFromTemplate()` | `*.hbs` files in `assets/Hbs/partials/` |

This means **there is no separate config file** for partials. The three index files are the config.

---

## CLI management — `CrazyCommand`

Partials are created and deleted through the CrazyPHP CLI:

```bash
# Create a new partial (interactive)
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new partial

# Delete one or more existing partials (interactive checkbox list)
php vendor/kzarshenas/crazyphp/bin/CrazyCommand delete partial
```

### `new partial` — what it does

Implemented in `CrazyPHP\Model\Partial\Create`. Steps in order:

1. **Prepare** — normalises the name (snake_case → PascalCase), checks no duplicate exists
2. **Create `.ts`** — renders `resources/Hbs/App/Partial/Partial.ts.hbs` → `app/Environment/Partials/<Name>.ts`
3. **Create `.scss`** — renders `resources/Hbs/App/Partial/Partial.scss.hbs` → `app/Front/style/scss/partial/_<name>.scss`
4. **Create `.hbs`** — renders `resources/Hbs/App/Partial/Partial.hbs.hbs` → `assets/Hbs/partials/<name>.hbs`
5. **Inject into `index.ts`** — adds the `import` line and the entry in `globalPartials`, both sorted by descending length
6. **Inject into `index.scss`** — adds `@import './partial/<name>'` inside the `// Partials` block, also sorted by descending length

### `delete partial` — what it does

Implemented in `CrazyPHP\Model\Partial\Delete`. The interactive list is built by calling `Partial::getSummary()`, which returns all currently known partials. Steps in order:

1. **Retrieve** — resolves each selected name to its three file paths via `Partial::get()`
2. **Remove from `index.ts`** — strips the `import` line and the `globalPartials` entry
3. **Remove from `index.scss`** — strips the `@import` line
4. **Move files to trash** — all three files (`.ts`, `.scss`, `.hbs`) are sent to `.trash/partial/<Name>/` via `Trash::send()`

---

## Backend rendering (PHP)

Page controllers declare a template path:

```php
public const TEMPLATE = "@app_root/app/Environment/Page/Home/template.hbs";
```

`CrazyPHP\Library\Template\Handlebars` compiles the template with LightnCandy, auto-loading every file from `assets/Hbs/partials/` as a named partial. Flags used:

```
FLAG_HANDLEBARSJS_FULL | FLAG_RUNTIMEPARTIAL | FLAG_PARENT | FLAG_ADVARNAME
```

Inside any page template, a partial is included with standard Handlebars syntax:

```handlebars
{{> my_partial}}
{{> sg_status state=Project.attributes.sg_status global=@root._ui.sg_status}}
```

---

## Frontend wiring (TypeScript)

### Registration

At app boot (`app/Front/index.ts`) all partial classes are collected into `globalPartials` and passed to `Crazyobject`:

```typescript
let globalPartials = {
    "my_partial": MyPartial,
    // … one entry per partial, keys sorted by descending length
};

window.Crazyobject = new Crazyobject({ globalPartials });
```

`Register.register()` validates that each value extends `Crazypartial` before adding it to the internal collection.

### Scanning

After a page injects HTML into the DOM, it calls:

```typescript
let scanned = window.Crazyobject.partials.scan(containerEl);
```

`Register.scan()` queries for every `[partial]` attribute inside the container, looks up the class by name, and returns an array of `RegisterPartialScanned` objects:

```typescript
{
    name: "my_partial",     // attribute value
    target: HTMLElement,    // the DOM node
    callable: MyPartial,    // the class constructor
    id: number              // unique timestamp-based id, written to data-partial-id
}
```

The calling page then instantiates each one:

```typescript
for (let partial of scanned) {
    new partial.callable(partial, options);
}
```

### Base class — `Crazypartial`

Every partial class extends `Crazypartial`. Key members:

| Member | Purpose |
|---|---|
| `input` | The `RegisterPartialScanned` object passed by the scanner |
| `html` | The compiled Handlebars template function (imported from the `.hbs` file via webpack) |
| `onReady()` | Called at construction and after a reload — attach DOM handlers here |
| `onDestroy()` | Called before a reload — clean up timers, listeners |
| `reload(state)` | Re-renders `html(state)`, replaces the DOM node in-place, calls `onDestroy()` then `onReady()` |
| `enable()` / `disable()` | Optional hooks for toggling the partial |
| `onChange(callable, options)` | Optional hook for change events |
| `getCurrentPageName()` | Returns the name of the currently active page |

### Minimal partial class

```typescript
import { Crazypartial } from "crazyphp";
const html = require("./../../../assets/Hbs/partials/my_partial.hbs");

export default class MyPartial extends Crazypartial {
    public html = html;

    public constructor(input: RegisterPartialScanned) {
        super(input);
        this.onReady();
    }

    public onReady = () => {
        // this.input.target is the DOM element
    }
}
```

### Loader pipeline (dynamic loading)

`Loader/Partial.ts` provides an alternative async loading path used when a partial must be loaded on demand rather than scanned from existing markup. It runs a sequential promise chain:

```
loadPartialDetail → loadPreAction → loadScript → loadContent → pushToDomEl → runScript → loadPostAction
```

---

## Webpack (build time)

`handlebars-loader` resolves `{{> partialName}}` at build time by looking in `assets/Hbs/partials/`. Helpers are loaded from two directories:

```javascript
// webpack.dev.js and webpack.prod.js
options: {
    partialDirs: ["./assets/Hbs/partials/"],
    helperDirs: [
        "./vendor/kzarshenas/crazyphp/resources/Js/Handlebars",
        "./assets/Js/Handlebars",
    ],
}
```

---

## Summary flow

```
CrazyCommand new partial
  └─ generates .ts / .scss / .hbs
  └─ injects into index.ts + index.scss

PHP controller renders page
  └─ Handlebars::load() picks up all assets/Hbs/partials/*.hbs
  └─ LightnCandy compiles template + partials → HTML

Browser receives HTML
  └─ page calls partials.scan(container)
  └─ for each [partial="name"] element → new MyPartial(scanned)
  └─ MyPartial.onReady() wires up interactivity
```
