# Pages

A Page is a full-screen view in the SPA. Like a Partial it is a three-file component (TypeScript, Handlebars template, SCSS), but it is **lazy-loaded** on navigation rather than embedded inside another template.

---

## File structure

Each page lives in its own folder under `app/Environment/Page/`:

```
app/Environment/Page/
└── Home/
    ├── index.ts        ← TypeScript class (extends Crazypage)
    ├── template.hbs    ← Handlebars template
    └── style.scss      ← SCSS styles
```

Page scripts are compiled by webpack into individual bundles:

```
dist/page/app/Home.<hash>.js
```

and loaded on demand when the user navigates to that page.

---

## CLI management — `CrazyCommand`

```bash
# Create a new page (interactive)
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new router

# Delete one or more pages (interactive checkbox list)
php vendor/kzarshenas/crazyphp/bin/CrazyCommand delete router
```

`new router` with type `app` generates:
1. `app/Environment/Page/<Name>/index.ts`
2. `app/Environment/Page/<Name>/template.hbs`
3. `app/Environment/Page/<Name>/style.scss`
4. `app/Controller/App/<Name>.php`
5. Adds the route to `config/Router.yml`

---

## Backend rendering (PHP)

The controller declares the template path and uses `State` to build the data passed to the template:

```php
class Home extends Controller {
    public const TEMPLATE = "@app_root/app/Environment/Page/Home/template.hbs";

    public function get(): void {
        $state = static::State();
        $state
            ->pushTitle("Home")
            ->pushResults($projects)
            ->pushContext()
            ->render();
    }
}
```

`State::render()` returns a JSON object the frontend stores as the page state.

---

## Frontend — TypeScript class

Every page class extends `Crazypage` and must:

1. Declare `className` (used by the loader to identify the page)
2. Import and assign `html` (the compiled template) and `css`
3. Implement `onReady()` — called after the page is mounted
4. Call `window.Crazyobject.register(ClassName)` at the bottom of the file

```typescript
import { Crazypage } from "crazyphp";
const html = require("./template.hbs");
const css  = require("./style.scss");

export default class Home extends Crazypage {

    public static readonly className: string = "Home";
    public static readonly html = html;
    public static readonly css  = css;
    public static readonly parameters: string[] = ["language"];

    public statePageEvents: stateEvent[] = [
        {
            name: "projects",
            selector: "Project",
            callback: (state, prevState) => { this._onProjectsChange(state, prevState); }
        },
    ];

    public constructor(options: LoaderPageOptions) {
        super(options);
        this.onReady();
        this.registerEvents(this.statePageEvents);
    }

    public onReady = (): void => {
        // DOM is ready, attach event listeners here
    }

    private _onProjectsChange = (state: any, prevState: any): void => {
        // React to state changes
    }
}

window.Crazyobject.register(Home);
```

---

## Base class — `Crazypage`

Key members of the abstract `Crazypage` class:

| Member | Purpose |
|---|---|
| `static className` | Unique page name — must match the route name |
| `static html` | Compiled Handlebars template function |
| `static css` | Compiled styles |
| `static parameters` | URL parameters declared by the route |
| `pageState` | Current page state from the Killa store |
| `statePageEvents` | Array of reactive state listeners |
| `onReady()` | Called on mount and after each reload |
| `registerEvents(events)` | Subscribes `statePageEvents` to the state store |
| `getPartial(name)` | Returns the `RegisterPartialScanned` for a partial in this page |
| `getAllPartials()` | Returns all scanned partials |
| `getPageState(forceRefresh?)` | Async — fetches state from the server |
| `redirectTo(path)` | Navigate to an absolute path |
| `redirectByName(name, options)` | Navigate using a route name |
| `static loadPageState(url?)` | Static loader used by `LoaderPage` |

---

## Page loading lifecycle (frontend)

`Loader/Page.ts` orchestrates navigation via a sequential promise chain:

```
loadPageDetail          initialise status flags
  ↓ loadPreAction       run optional pre-navigation hook
  ↓ loadUrl             resolve route name → URL
  ↓ openNewTab          open in new tab if requested
  ↓ loadPageCacheManager prepare IndexedDB cache
  ↓ loadScript          dynamically inject dist/page/app/<Name>.<hash>.js
  ↓ updateUrl           push new URL to browser history
  ↓ loadPageState       fetch JSON state from server
  ↓ updateTitle         set document.title
  ↓ cleanPotentialExisitingPartials  destroy old partial instances
  ↓ loadStyle           inject compiled CSS
  ↓ loadContent         render template with state data
  ↓ registerInHistory   pushState for back/forward
  ↓ applyColorSchema    apply Material Dynamic Colors
  ↓ scanPartials        find [partial="…"] elements, instantiate TS classes
  ↓ loadOnReadyScript   new PageClass(options) → onReady()
  ↓ loadPostAction      run optional post-navigation hook
  ↓ setCurrentPage      store as window.Crazyobject.currentPage
```

### Global hooks

Pre/post actions can be set once in `app/Front/index.ts` and apply to every navigation:

```typescript
LoaderPage.setGlobalPreAction(options => {
    // show preloader
});
LoaderPage.setGlobalPostAction(options => {
    // hide preloader
});
```

---

## Registration in `app/Front/index.ts`

Pages are **not** pre-imported in `index.ts` (unlike partials). They self-register at the bottom of their own file via:

```typescript
window.Crazyobject.register(Home);
```

`RegisterPage.register()` stores the class reference keyed by `className` so the loader can find it after the script bundle is injected.

---

## Summary flow

```
CrazyCommand new router (type: app)
  └─ generates index.ts / template.hbs / style.scss / Controller.php
  └─ adds route to config/Router.yml

User navigates → LoaderPage chain runs
  └─ loadScript → injects dist/page/app/<Name>.<hash>.js
  └─ loadPageState → GET /<route>?catch_state=true → JSON
  └─ loadContent → renders template.hbs(state) → innerHTML
  └─ scanPartials → instantiates partial TS classes
  └─ loadOnReadyScript → new Home(options) → onReady()
```
