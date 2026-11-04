# Remembering form values

Persistence is opt-in per form item. Add `"persist" => true` only to fields that should be remembered:

```php
"items" => [
    ["name" => "scenario", "type" => "select", "persist" => true],
    ["name" => "filepath", "type" => "text"],
],
```

Omitted or false `persist` values are never saved or restored. No form-level persistence configuration is required. Passwords, hidden submission values and file inputs are never saved, even when marked.

The shared template passes opted-in item names to the frontend. A caller can provide `persistence: {scope: "user-id:project-id"}` in `FormOptions` to isolate values per user and project, or `persistence: false` to disable storage. Use a stable form ID.

Values are saved on native input/change events using `Crazycache("form-values", {driver: "localStorage"})`. They survive popup dismissal and page reload. Storage failures do not prevent using the form.

Parents restore before dependent widgets initialize. Remote choices are restored only when available in the current source. Changing a parent invalidates its remembered child. Disabled scenario fields retain their last remembered values.

The form emits `crazyform:restored` after native restoration. Use `form.ready` to await normal initialization.
