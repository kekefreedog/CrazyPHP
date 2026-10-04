# Forms

Forms are rendered via the `form` Partial. The PHP backend builds a form definition array, pushes it into the page state, and the Handlebars template renders the inputs. The TypeScript `Form` partial class then manages submission, reset, and change events.

---

## How a form reaches the page

### 1. Define the form (PHP)

Form definitions are constants in `app/Library/Form.php`:

```php
class Form {
    public const LOGIN_FORM = [
        "id"    => "login",
        "title" => "Login",
        "post"  => "/api/v1/auth/login",
        "items" => [
            [
                "name"        => "username",
                "type"        => "text",
                "label"       => "Username",
                "required"    => true,
                "placeholder" => true,
            ],
            [
                "name"     => "password",
                "type"     => "password",
                "label"    => "Password",
                "required" => true,
            ],
        ],
    ];
}
```

### 2. Push the form to the page state (controller)

```php
$state = static::State();
$state
    ->pushForm(Form::LOGIN_FORM)
    ->pushContext()
    ->render();
```

`State::pushForm()` passes the array to `CrazyPHP\Library\State\Components\Form`, which normalises the items and stores the result in `_ui.forms`.

### 3. Render in the template

The form is available in the Handlebars template via `_ui.forms`:

```handlebars
{{#each _ui.forms}}
  <div partial="form" data-id="{{this.id}}"></div>
{{/each}}
```

The `partial="form"` attribute tells the scanner to instantiate the TypeScript `Form` class for this element. The form definition is retrieved from the page state by matching the `data-id`.

---

## Form definition schema

| Field     | Type               | Purpose                                                    |
| --------- | ------------------ | ---------------------------------------------------------- |
| `id`      | string             | Unique identifier, used to match template element to state |
| `title`   | string             | Heading displayed above the form                           |
| `post`    | string             | API endpoint for form submission                           |
| `entity`  | string?            | Entity name used by the backend processor                  |
| `onready` | string?            | JS expression evaluated on form mount                      |
| `reset`   | bool               | Show reset button                                          |
| `confirm` | bool/string/object | Show confirmation dialog before submit                     |
| `items`   | array              | Field definitions (see below)                              |

### Item schema

| Field         | Type               | Values                                                                                                |
| ------------- | ------------------ | ----------------------------------------------------------------------------------------------------- |
| `name`        | string             | Field name (sent as POST key)                                                                         |
| `type`        | string             | `text` `email` `password` `number` `date` `color` `file` `select` `checkbox` `radio` `switch` `range` |
| `label`       | string             | Display label                                                                                         |
| `placeholder` | bool/string        | `true` = use label as placeholder                                                                     |
| `required`    | bool               | —                                                                                                     |
| `readonly`    | bool               | —                                                                                                     |
| `disabled`    | bool               | —                                                                                                     |
| `default`     | string/bool/number | Pre-filled value                                                                                      |
| `multiple`    | bool               | Multi-value (select, file)                                                                            |
| `depends`     | string             | Show field only when another field has a non-empty value                                              |
| `select`      | array/object       | Options list or remote request config (for `type: select`)                                            |
| `_style`      | object             | CSS class overrides per element (wrapper, label, input…)                                              |

### Remote select

```php
"select" => [
    "url"    => "/api/v1/projects",
    "method" => "GET",
    "label"  => "name",      // field to use as option label
    "value"  => "id",        // field to use as option value
]
```

TomSelect is used under the hood and loads options lazily.

---

## TypeScript Form partial

`app/Environment/Partials/Form.ts` wraps `UtilityForm` from CrazyPHP.

### Widget adapters

Forms keep their existing input handlers by default. To opt into kmaterialize,
install the optional `kmaterialize` peer dependency (a build exposing the enhanced
form widgets) in the consuming application, and pass the adapter option:

```typescript
import {Form} from "crazyphp";

const form = new Form(formElement, {
    adapter: "kmaterialize",
    adapterOptions: {
        richTextarea: {
            fontFamily: "sans-serif",
            // Optional stylesheet URL provided by the application.
        },
    },
});

await form.ready;
// When the page or partial is destroyed:
form.destroy();
```

Adapter styles live in `app/Front/style/scss/adapter/kmaterialize.scss`.
`resources/Yml/Structure.yml` copies this file from
`resources/Scss/style/adapter/kmaterialize.scss` when scaffolding an application.
Load it after the widget base styles and kmaterialize's CSS. From the form SCSS
partial, use `@use '../adapter/kmaterialize';`. The single stylesheet includes
form layout, filter controls and body-appended dropdown styles.

The adapter implementation lives in `Utility/Form/Adapter/Kmaterialize.ts` and
implements `src/Interface/CrazyFormAdapter.ts`. Application `initializeInput`
callbacks run first; returning `true` skips both the adapter and the existing
handler. When the adapter returns `false`, the existing handler initializes the
field. Remote and dependent selects continue to use the existing handlers.
Serialization, submission and filter operators remain managed by `Form`.

`ready` resolves after widget initialization and form event registration, or
rejects if initialization fails. `destroy()` stops subsequent initialization
stages, destroys adapter widgets and removes form-owned listeners. Call it before
replacing the form element; the scaffold form partial does this in `onDestroy`.
Existing legacy input handlers retain their own widget lifecycle behavior.

The adapter module is loaded only when selected. Bundlers that resolve all dynamic
imports or type-check library sources may still require the optional peer at build
time, even if the application uses only the default handlers. The main CrazyPHP
entrypoint exports adapter interfaces as types without eagerly importing widgets.

### Usage from a page

```typescript
// Get the form partial instance
let formPartial = this.getPartial("form");

// Listen to submission
formPartial.scriptRunning.onSubmit((result) => {
  console.log(result); // parsed form data
});

// Listen to any field change
formPartial.scriptRunning.onChange((result, options) => {
  // result = current form values
});

// Listen to reset
formPartial.scriptRunning.onReset(() => {});

// Read current values
let data = formPartial.scriptRunning.getFormData();
```

### Key methods

| Method                        | Purpose                                                  |
| ----------------------------- | -------------------------------------------------------- |
| `onSubmit(callback)`          | Called on valid submission; receives parsed field values |
| `onReset(callback)`           | Called when the reset button is clicked                  |
| `onChange(callback, options)` | Called on any field change                               |
| `getFormData()`               | Returns current field values as an object                |

---

## PHP form processing

When an API controller receives a form submission it uses `CrazyPHP\Library\Form\Process` to sanitise and type-cast the raw input:

```php
$inputs = $this->getHttpRequestData();
$processed = Process::getResultSummary($inputs["items"]);
```

### Built-in processors (VARCHAR type)

Applied via the `process` array in `REQUIRED_VALUES` or form item definitions:

| Processor      | Effect                       |
| -------------- | ---------------------------- |
| `trim`         | Strip whitespace             |
| `clean`        | Remove special characters    |
| `cleanPath`    | Sanitise as a file path      |
| `strtolower`   | Lowercase                    |
| `ucfirst`      | Capitalise first letter      |
| `ucwords`      | Capitalise each word         |
| `camelToSnake` | `myField` → `my_field`       |
| `snakeToCamel` | `my_field` → `myField`       |
| `email`        | Validate as e-mail address   |
| `bool`         | Cast to boolean              |
| `https`        | Ensure URL starts with https |

### Validation

`CrazyPHP\Library\Form\Validate` runs before processing and throws a `CrazyException` on failure. Built-in validators:

| Validator              | Checks                         |
| ---------------------- | ------------------------------ |
| `isEmail`              | FILTER_VALIDATE_EMAIL          |
| `isIpAddress`          | FILTER_VALIDATE_IP             |
| `isValidUrl`           | FILTER_VALIDATE_URL            |
| `isValidFile`          | `$_FILES` array structure      |
| `isSemanticVersioning` | SemVer format                  |
| `isMobilePhone`        | Phone number pattern           |
| `isRegex`              | Valid regex pattern            |
| `isStaticMethod`       | `ClassName::methodName` format |

---

## Supported input types and their integrations

| Type                                      | UI library                      |
| ----------------------------------------- | ------------------------------- |
| `date`                                    | Easepick / Air Date Picker      |
| `select`                                  | TomSelect (with remote loading) |
| `file`                                    | Filepond                        |
| `color`                                   | Native color picker             |
| `switch` / `range` / `checkbox` / `radio` | Custom SCSS components          |

---

## Data flow summary

```
app/Library/Form.php       form definition constant
  ↓
Controller::pushForm()     normalised by State\Components\Form
  ↓
State::render()            → JSON: _ui.forms[…]
  ↓
template.hbs               <div partial="form" data-id="…">
  ↓
Register.scan()            → new Form(scanned)
  ↓
Form.ts (Crazypartial)     wraps UtilityForm, exposes onSubmit/onChange
  ↓
User submits               POST to form.post URL
  ↓
API controller             Process::getResultSummary() → Validate → logic
```

## OTP alignment (kmaterialize)

OTP fields default to centered alignment. Set `_style.otp.align` to `left`, `center`, or `right` to position the slots within the field; the label starts at the first OTP slot for every alignment.

```php
[
    "name" => "auth_token",
    "type" => "otp",
    "label" => "Token",
    "_style" => [
        "otp" => ["length" => 6, "align" => "center"],
        "customClass" => ["input-field" => "outlined"],
    ],
]
```

The partial emits `data-otp-align`. Direct kmaterialize users can pass `OtpInput.init(input, {align: "right"})`; explicit JavaScript options take precedence over the attribute. The form must use the `kmaterialize` adapter.
