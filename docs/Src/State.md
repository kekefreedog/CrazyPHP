# State

State is the mechanism that passes data from the PHP backend to the frontend on every page load and API response, and then keeps the frontend reactive to updates. The PHP side builds a state object; the frontend stores it in a Killa store and fires callbacks when slices of it change.

---

## PHP — building the state

Every controller has access to `State` via the inherited `static::State()` helper:

```php
$state = static::State();
$state
    ->pushTitle("My Page")
    ->pushResults($queryResults)
    ->pushForm(Form::MY_FORM)
    ->pushPartialState("workstation_collection", $workstations)
    ->pushContext()
    ->render();
```

`render()` serialises everything into a JSON structure and sends it as the HTTP response.

### `Library\State\Page` — key methods

| Method | Purpose |
|---|---|
| `pushTitle(string $title)` | Set `document.title` on the frontend |
| `pushColorSchema(string $source)` | Set Material Dynamic Colors source color |
| `pushResults(mixed $result, ?string $entity)` | Push query results (main data) |
| `pushResultsWithKey(string $key, mixed $result)` | Push results at a specific dot-notation path |
| `pushForm(array $form)` | Add a form definition to `_ui.forms` |
| `pushPartialState(string $id, mixed $data)` | Push data for a specific partial into `_ui.partials` |
| `pushUiContent(string $key, mixed $value)` | Set arbitrary data under `_ui` |
| `pushRedirection(string $nameOrUrl, ...)` | Schedule a page redirect event |
| `pushEvent(array $event)` | Push a generic event (modal, redirect, etc.) |
| `pushContext()` | Include app context (user, permissions) in the response |
| `pushCookie()` | Include cookie values |
| `pushConfig()` | Include selected config values |
| `pushError(array $options)` | Add an error to the response |
| `pushException(Exception $e)` | Convert an exception to a standard error |
| `setStatusCode(int $code, array $opts)` | Set the HTTP response status code |
| `render()` | Finalise and return the state array |

### State object shape

```json
{
  "results": { … },
  "errors": [ … ],
  "_context": { … },
  "_cookies": { … },
  "_config": { … },
  "_ui": {
    "title": "My Page",
    "forms": [ … ],
    "partials": {
      "workstation_collection": { … }
    }
  },
  "_events": [ … ]
}
```

---

## Frontend — the Killa store (`State.ts`)

`State` is a singleton wrapping the Killa reactive store. It is initialised once by `Crazyobject` at boot.

Default root keys:

| Key | Holds |
|---|---|
| `_page` | Per-page state, keyed by page class name |
| `_global` | App-wide state (language, user, etc.) |
| `_partial` | Global partial state shared across pages |

### Core API

```typescript
import { State } from "crazyphp";

// Read / write arbitrary path
State.get().data("_page.Home.results");
State.get().data("_page.Home.results", newValue);

// Page-scoped shorthand (auto-resolves current page name)
State.get().page();                 // entire page state
State.get().page("Home");           // specific page
State.get().page("Home", newData);  // update

// Global partial state
State.get().globalPartial("workstation_collection");
State.get().globalPartial("workstation_collection", data);

// Full store snapshot
State.get().all();

// Register a reactive listener
State.get().event("myEvent", callback, "_page.Home.results");

// Clear all state
State.get().reset();
```

Paths use dot notation: `_page.Home._ui.partials.workstation_collection`.

---

## Page integration — `Crazypage`

Pages connect to the state store via `statePageEvents` and `registerEvents()`.

```typescript
export default class Settings extends Crazypage {

    public statePageEvents: stateEvent[] = [
        {
            name: "workstations",
            selector: "_ui.partials.workstation_collection",
            callback: (state, prevState) => {
                // called whenever that path changes
                let partial = this.getPartial("workstation_collection");
                partial?.scriptRunning?.reload({ state, global: state });
            }
        },
        {
            name: "search",
            selector: "search",
            callback: (state, prevState) => { /* … */ }
        },
    ];

    public constructor(options: LoaderPageOptions) {
        super(options);
        this.onReady();
        this.registerEvents(this.statePageEvents);
    }

    public onReady = (): void => { /* mount logic */ }
}
```

`registerEvents()` subscribes each entry to the Killa store. The `selector` is a dot-notation path relative to the current page's slice of `_page`. When that path changes, `callback(newState, prevState)` fires.

### Reading the initial state in `onReady`

```typescript
// Sync — already in store after page load
let state = this.pageState;

// Async — force a fresh fetch from the server
let freshState = await this.getPageState(true);
```

---

## Partial state updates

The most common pattern for reactive partials:

**PHP controller (API endpoint):**
```php
$state = static::State();
$state
    ->pushPartialState("workstation_collection", $updatedData)
    ->render();
```

**TypeScript page event handler:**
```typescript
{
    name: "workstations",
    selector: "_ui.partials.workstation_collection",
    callback: (state, prevState) => {
        this.getPartial("workstation_collection")
            ?.scriptRunning
            ?.reload({ state, global: state });
    }
}
```

`partial.scriptRunning.reload(state)` re-renders the Handlebars template with the new data and calls `onReady()` on the partial class.

---

## State recovery (catch_state)

When the frontend navigates to a page it requests the state with `?catch_state=true` appended to the URL. On the PHP side this activates a special execution mode: the controller runs normally but throws a `CatchState` exception at `render()` time, which is caught by the framework to return pure JSON instead of a full HTML page.

This means the same controller method handles both:
- **Full page load** (browser navigation) → returns HTML with embedded initial state
- **SPA navigation** → returns JSON state only

---

## Global state

Data that must survive page transitions is stored in `_global`:

```typescript
// Written at boot (app/Front/index.ts)
window.Crazyobject = new Crazyobject({
    globalStateCollection: {
        "language": Crazylanguage.getNavigatorLanguage()
    }
});

// Read anywhere
State.get().data("_global.language");
```

---

## Data flow summary

```
Controller::pushPartialState("wsc", $data)
  └─ State::render() → JSON: { _ui: { partials: { wsc: … } } }

Frontend receives JSON (catch_state)
  └─ State.get().page("Settings", json) → stored in Killa

statePageEvents["workstations"] fires
  └─ selector matches "_ui.partials.workstation_collection"
  └─ callback(newState, prevState)
       └─ partial.scriptRunning.reload(newState) → re-renders DOM
```
