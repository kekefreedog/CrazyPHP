# **_Api_** **Schema of request**

## Description

- Full details and good practice on how send request to api.

## Options allowed

| Description         | Option 1 | Option 2 |
| ------------------- | -------- | -------- |
| Filters collection  | filters  | filter   |
| Sort parameters     | sort     | sorting  |
| Group parameters    | group    | grouping |
| Facultative options | option   | options  |

## Exemples of request

```ts
let body = {
    filters: [
        name: "Home"
    ],
    sort: "asc",
    options: [
        limit: 1 // Limit number of item by one,
        fields: name,
        arguments: {
            language: "fr"
        }
    ]
}
```