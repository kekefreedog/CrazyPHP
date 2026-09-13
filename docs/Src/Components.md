# Web components — Crazycomponent2

`Crazycomponent2` builds custom elements from three co-located files: `index.ts`,
`template.hbs`, and `style.scss`. It is exported alongside the original
`Crazycomponent`, so existing components can migrate individually.

The complete example is in
[`resources/Environment/Component/ExampleCard2`](../../resources/Environment/Component/ExampleCard2).
Copy that directory into your application's `app/Environment/Component` directory.
It is an opt-in example; the existing project scaffolding is unchanged.

## Define and register a component

```ts
import { Crazycomponent2, type Crazycomponent2Properties } from "crazyphp";

interface CardValues {
    label: string;
    count: number;
}

export default class ExampleCard2 extends Crazycomponent2<CardValues> {
    static options = { shadow: false };
    static template = require("./template.hbs");
    static styles = require("!!css-loader!sass-loader!./style.scss");
    static properties = {
        label: { type: "string", default: "Example card" },
        count: { type: "number", default: 0, reflect: true },
    } satisfies Crazycomponent2Properties;
}
```

Add it to the existing application's `globalComponentsCollection`:

```ts
import ExampleCard2 from "../Environment/Component/ExampleCard2";

const globalComponentsCollection = {
    "example-card-2": ExampleCard2,
    // Existing components...
};
```

Alternatively, register once with `customElements.define("example-card-2", ExampleCard2)`.
Use the element in an application template:

```html
<example-card-2 label="Review" count="2">
    <strong slot="heading">Review notes</strong>
    <p>This content survives component updates.</p>
</example-card-2>
```

Webpack compiles HBS through the existing `handlebars-loader` configuration,
including its configured helpers and partials. The explicit CSS loader chain
returns CSS to the component instead of injecting it into the document itself.
Compiled functions, strings, and their `{ default: ... }` module wrappers are
accepted for templates. Styles accept CSS strings, context functions returning
CSS strings, and css-loader exports with a CSS-aware `toString()` method.

## Enable or disable Shadow DOM

```ts
static options = { shadow: true };  // Open shadow root
static options = { shadow: false }; // Light DOM, the default
```

The rendering mode is fixed when an instance is constructed. For a component
that already has a constructor, `super({ shadow: true })` overrides its static
option. Do not try to switch an existing element between modes.

Use `this.renderRoot.querySelector(...)` in component code in both modes.
`renderRoot` is the element itself in light DOM, or its open shadow root.

| Behavior | Light DOM | Shadow DOM |
| --- | --- | --- |
| Global styles such as Materialize | Apply to generated markup | Must be supplied inside the shadow root if needed |
| Component styles | Use unique classes or host-prefixed selectors to avoid leaking | Scoped by the shadow root |
| Styling the host | `example-card-2 { ... }` | `:host { ... }` |
| Supplied children | Moved into the matching template `<slot>` | Stay on the host; browser performs native slot assignment |
| Slot APIs and `slotchange` | Not emulated | Native |

The example SCSS works in either mode without depending on global styles.
Existing styles such as `regular-btn a` need adaptation for shadow mode: the
host is outside the shadow tree, so use `:host` and local selectors instead.

## Properties and attributes

Declare the schema in **static** `properties`. Values are stored per instance;
array and object defaults are copied recursively. Use lowercase type names:
`string`, `number`, `boolean`, `array`, and `object`.

| Schema option | Meaning |
| --- | --- |
| `default` | Initial value; also used for removed or invalid attributes |
| `attribute: true` or omitted | Observe the lowercased property name |
| `attribute: "image-url"` | Observe an explicit lowercase attribute name |
| `attribute: false` | A value controlled only through TypeScript |
| `reflect: true` | Write `setProperty()` changes to the mapped attribute |
| `select: [...]` | Allowed scalar values; supply a default in this list |

There is no manually maintained `observedAttributes` list. Declare schemas before
registration and treat them as immutable. Subclasses inherit declarations;
overriding `static properties` replaces the schema, so spread the parent's schema
if you want to extend it.

Default values, when omitted, are `""`, `0`, `false`, `[]`, and `{}` respectively.
Use JSON-compatible arrays and plain objects. Attribute conversion works as follows:

- Strings retain whitespace, including empty strings.
- Numbers must be finite; blank or invalid numbers use the default.
- Boolean attributes accept `""`, `"true"`, and `"1"` as true, and `"false"` and
  `"0"` as false, ignoring surrounding whitespace and letter case. Other values
  use the default. This deliberately supports the explicit boolean strings used
  in existing CrazyPHP components, rather than HTML's presence-only convention.
- Arrays and objects are parsed as JSON and checked for the declared shape.
  Invalid JSON, `null`, or the wrong shape use the default.
- Removing an attribute restores its declared default.

`setProperty()` requires an already typed value; invalid values and unknown names
throw `TypeError`. Reflected booleans use `"true"`/`"false"` strings; reflected
arrays and objects use JSON. Reflection defaults to false and requires an attribute.

```ts
card.setProperty("count", 3);
console.log(card.getProperty("count")); // 3
await card.updateComplete;
```

The optional class generic, such as `Crazycomponent2<CardValues>`, types the
getter and setter. Keep that interface consistent with the static schema.
Array/object changes are detected by reference. Replace the value to update it,
or call `requestUpdate()` after an in-place mutation.

## Rendering and cleanup

The template context is `{ attributes, name }`, preserving existing HBS expressions:

```hbs
<h2>{{attributes.label}}</h2>
<output>{{attributes.count}}</output>
<slot></slot>
```

Normal HBS expressions escape text. Triple braces intentionally render raw HTML;
use them only for content you trust. Whitespace is preserved by the component.

Rendering is scheduled in a microtask. Multiple synchronous changes produce one
render. Await `updateComplete` after changing values or connecting the element;
it resolves true if rendered and false if disconnected before the update ran.
Render errors reject this promise. `requestUpdate()` returns the same promise
for an already queued update. An update requested inside `postRender()` schedules
a subsequent render with a new promise.

`render()` returns just the HTML without mounting it. `prepareContext()` may be
overridden to add template data. `setHtmlAndCss(template, styles)` is also
available for constructor-based asset configuration. Do DOM-dependent work in
`postRender()`, after subclass fields and the template have been initialized.

```ts
public postRender(): void {
    const button = this.renderRoot.querySelector<HTMLButtonElement>("button")!;
    const click = () => this.setProperty("count", this.getProperty("count") + 1);
    button.addEventListener("click", click);
    this.onCleanup(() => button.removeEventListener("click", click));

    // For a widget: this.onCleanup(() => widget.destroy());
}
```

Cleanup runs in reverse registration order before rerendering and on disconnect.
Reconnect rebuilds the template and runs `postRender()` again. If overriding
native lifecycle callbacks, call their `super` implementations.

## Child preservation and limits

Both modes preserve supplied child node identity, text, input values, and listeners
across updates. Named slots and fallback content are supported. New children can
be appended to the host after rendering, and removed children are not resurrected.

Light DOM projection runs asynchronously through a `MutationObserver`. Only the
first slot with a given name receives matching children. Unmatched children are
retained in a detached fragment until a matching slot appears or their `slot`
attribute changes. Keep a node reference if you need to manipulate an unmatched
child. Slots owned by nested custom elements are not used by the parent.

Light DOM is a projection convention, not a full Shadow DOM polyfill. It moves
supplied nodes during rendering, so nested custom elements can receive disconnect
and reconnect callbacks. Generated template nodes are replaced in either mode;
focus and widget state inside those generated nodes are not automatically preserved.
Avoid using `innerHTML` on the host to update a mounted component: change its
properties or append/remove supplied nodes instead.

## Migrating from Crazycomponent

1. Import and extend `Crazycomponent2`.
2. Move instance `properties` to `static properties`, rename `value` to `default`,
   and replace `bool` with `boolean`. Remove manual `observedAttributes`.
3. Set `static template` and `static styles`, or keep `setHtmlAndCss()`.
4. Replace `getCurrentAttribute()` with `getProperty()` and use `setProperty()`
   for programmatic updates. Use native `hasAttribute()` when testing HTML presence.
5. Remove `allowChildNodes`; child preservation is built in. Choose `options.shadow`.
6. Query through `renderRoot` and pair widget/listener setup with `onCleanup()`.
7. Await `updateComplete` where code previously expected synchronous rendering.

RegularBtn is a useful first migration for attributes and tooltip cleanup.
RegularCard exercises named/default content slots. Existing components and the
Rodeo application's source files are not changed by adding the new base class.

## Verification

```sh
node --import tsx --test tests/Front/Library/Crazycomponent2.test.ts
npm test
```

These tests cover compiled HBS, CSS export normalization, typed properties,
reflection, initial upgrades, both DOM modes, child preservation, and cleanup.
