# Runner

A Runner is a multi-step async task that executes every method whose name starts with `run` in declaration order. Each step receives and returns a shared `RunnerOption` object, allowing data to accumulate as it flows from one step to the next. Runners are used in pages and partials to orchestrate complex operations — API calls, data transforms, DOM updates — behind an optional progress UI.

---

## How it works

```
new MyRunner(extra).execute()
        │
        ├─ setUpBeforeClass()          one-time setup
        │
        ├─ [for each run* method:]
        │   ├─ setUpBeforeMethod()     called before every step
        │   ├─ runStepOne()            ← your business logic
        │   └─ tearDownAfterMethod()   called after every step
        │
        └─ tearDownAfterClass()        one-time teardown
```

`execute()` discovers every method whose name starts with `run` (via prototype walking), chains them as Promises in declaration order, and resolves with the final `RunnerOption`. If any step calls `this.stop()`, a `RunnerError` is thrown and the chain exits cleanly. Any other `Error` is forwarded to the optional viewer.

---

## `RunnerOption` — the shared state object

Every `run*` method receives and must return a `RunnerOption`:

```json
{
    "result": {
        "dataA": "[...]",
        "dataB": "[...]"
    },
    "extra": {},
    "_info": {
        "status": "In Progress",
        "name": "My Runner",
        "run": {
            "total": 4,
            "current": 2,
            "name": [
                { "method": "runStepA", "label": "Run Step A" },
                { "method": "runStepB", "label": "Run Step B" }
            ]
        }
    },
    "_viewer": null
}
```

| Field | Type | Purpose |
|---|---|---|
| `result` | `object \| null` | Accumulated output — steps read and write their data here |
| `extra` | `any` | Input passed at construction: `new MyRunner(extra)` |
| `_info.status` | `string` | `"Waiting"` → `"Ready"` → `"In Progress"` → `"Complete"` |
| `_info.name` | `string` | Display name set by `setName()` in the constructor |
| `_info.run.total` | `number` | Total number of `run*` methods discovered |
| `_info.run.current` | `number` | Index of the currently executing step |
| `_info.run.name` | `array` | All step method names with human-readable labels |
| `_viewer` | `RunnerViewer \| null` | Active progress viewer instance (or `null`) |

---

## Creating a runner

```typescript
import { UtilityRunner } from "crazyphp";

export default class MyRunner extends UtilityRunner implements CrazyRunner {

    public readonly name: string = "My runner";

    public constructor(extra: any = null) {
        super(extra);            // extra is accessible as options.extra in every step
        this.setName(this.name);
    }

    // Steps execute in the order they are declared in the class

    public runFetchData = async (options: RunnerOption): Promise<RunnerOption> => {
        const res  = await fetch('/api/v1/something');
        options.result = { data: await res.json() };
        return options;
    }

    public runProcessData = async (options: RunnerOption): Promise<RunnerOption> => {
        // options.result.data is available from the previous step
        options.result["processed"] = options.result["data"].map(/* ... */);
        return options;
    }

    public runUpdateDom = async (options: RunnerOption): Promise<RunnerOption> => {
        document.getElementById("my-el")!.innerHTML = options.result["processed"];
        return options;
    }

}
```

### With a progress viewer

Pass a viewer class as the second argument to `super()`. The viewer must implement `RunnerViewerConstructor`:

```typescript
import MyViewer from "./MyViewer";

public constructor(extra: any = null) {
    super(extra, MyViewer);
    this.setName(this.name);
}
```

The viewer opens automatically before the first step, updates its label after each step, and closes when the chain finishes (or on error).

---

## Lifecycle hooks

All four are no-ops in the base class — override only what you need:

```typescript
// Called once before the first run* method
public setUpBeforeClass = async (options: RunnerOption): Promise<RunnerOption> => {
    // e.g. validate options.extra, show a custom spinner
    return options;
}

// Called before every run* method
public setUpBeforeMethod = async (options: RunnerOption): Promise<RunnerOption> => {
    return options;
}

// Called after every run* method
public tearDownAfterMethod = async (options: RunnerOption): Promise<RunnerOption> => {
    return options;
}

// Called once after the last run* method (or on any error)
public tearDownAfterClass = async (options: RunnerOption): Promise<RunnerOption> => {
    // e.g. hide a loading spinner, reset UI state
    return options;
}
```

---

## Stopping early

Call `this.stop()` inside any `run*` method to abort the chain cleanly. The viewer closes, `tearDownAfterClass` fires, and a `RunnerError` is thrown (caught internally — will not surface as an unhandled rejection):

```typescript
public runValidate = async (options: RunnerOption): Promise<RunnerOption> => {
    if(!options.extra?.id) {
        this.stop(options, "No ID provided");
    }
    return options;
}
```

An optional callback can run before the chain exits:

```typescript
this.stop(options, "Reason", (opts) => {
    // last-chance cleanup
});
```

---

## Executing a runner

Instantiate and call `execute()`. Pass `extra` for any input the steps need:

```typescript
// Triggered from a page's onReady() or an event handler:
const runner = new MyRunner({ id: 42 });
runner.execute();
```

`execute()` returns a `Promise<RunnerOption>` — chain `.then()` if you need to act on the final result:

```typescript
new MyRunner(extra)
    .execute()
    .then(options => console.log(options.result));
```

---

## Partials inside a runner

Runners inherit helpers for scanning and managing partials within a DOM element:

```typescript
// Scan a container, instantiate all [partial="..."] elements found inside it
const found = this.loadPartials(containerEl);

// Retrieve a previously loaded partial by name
const myPartial = this.getPartialName("my_partial");
// → returns RegisterPartialScanned[] | null

// Clear the internal partial registry
this.cleanPartials();
```

---

## Key imports

| Symbol | Source | Purpose |
|---|---|---|
| `UtilityRunner` | `crazyphp` | Base class — extend this |
| `CrazyRunner` | global type | Interface — implement this (enforces `name` property) |
| `RunnerError` | `crazyphp` | Thrown by `stop()`, caught internally |
| `Crazyrequest` | `crazyphp` | HTTP client used inside `run*` steps |
