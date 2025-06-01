---
runme:
  id: 01HJTNJC60GAMR6TQV7XXB1C5Z
  version: v2.2
---

# ___Api___ __Schema of request__

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

```ts {"id":"01HJTNJC60GAMR6TQV7X27H5KQ"}
let body = {
    filters: [
        name: "Home"
    ],
    data: object|Array<any>
    sort: "asc",
    options: [
        limit: 1 // Limit number of item by one,
        offset: 1 // Offset on limit (can be cumulate with page)
        page: 1 // Offset on limit (can be cumulate with offset)
        fields: string|Array<string>,
        arguments: {
            language: "fr"
        },
        pageStateProcess: true // Process value into _pageStateProcess private methods to add _metadainfo...
    ]
}
```

## Batching

Batching allow you to send multiple requests as a single transaction.

By default, supported request are : `create`, `delete`, `update`

Exemple :

```json {"id":"01HM7MGZ911E1CACD7M8SVYH1Z"}
{
    "requests": [
        {
            "type": "create",
            "entity": "Shows",
            "body": {
                "key": "value"
            }
        },
        {
            "type": "update",
            "id": 0,
            "entity": "Shows",
            "body": {
                "key": "value"
            }
        },
        {
            "type": "delete",
            "entity": "Shows",
            "id": 0,
        },
    ],
}
```