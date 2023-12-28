# Runner

Script that allow to execute multiple method starting by "run" one by one

## Options schema

Schema of the options passed on each method

```json
{
    "errors": [
        // Simple error
        {
            "code": 500,
            "type": "warn",
            "detail": "Message of the error",
            "_run": {
                "name": "...",
                "id": "...",
                "position": "...",
            }
        },
    ],
    "result": {
        "dataA": "[...]",
        "dataB": "[...]"
    }
    "_info": {
        "run": {
            "total": 10,
            "current" : 1, // Or null
            "name": [
                {
                    "method": "runStepA",
                    "label": "Run Step A"
                }
            ]
        },
    },
    "_user_interface": {
        "preloader":{

        }
    }
}
```

## Method before & after

## Execute runner

## Exemple