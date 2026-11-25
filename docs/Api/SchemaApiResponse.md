# ***Api*** **Schema of response**

## HTTP format and compression

With a mode-specific `Router.parameters.api.format`, `CrazyPHP\Core\ApiResponse` uses the entry for `Front.lastBuild.mode` regardless of the request's `Accept` header. The starter configuration sends MessagePack in production and JSON in development.

```yaml
Router:
  parameters:
    api:
      format:
        production: msgpack
        development: json
```

With a single JSON or MessagePack format such as `format: json`, the configured value is the default. Clients can select either representation through `Accept`. Weighted preferences and wildcards are supported. An absent header or a tie uses the configured default. Excluding both formats produces HTTP 406. A controller can select its own representation with `setContentType()`.

A client advertising `Accept-Encoding: gzip` can receive either format with HTTP gzip compression. CrazyPHP considers gzip for serialized bodies of at least 1,024 bytes. Small or incompressible responses use identity when permitted. A client excluding identity may receive gzip below the threshold. Requests excluding all available content codings receive HTTP 406. Responses vary on the negotiation headers so shared caches can distinguish the variants.

Browsers decode HTTP gzip automatically. Pass the resulting bytes directly to the MessagePack parser, or call `response.json()` for JSON. The API envelope is identical in both formats.

## Version 1

### Description

- Full details and good practice on how parameters available in api response.

### Response Json

```json
{
    // Errors
    "errors": [
        // Simple error
        {
            "code": 500,
            "type": "warn",
            "detail": "Message of the error"
        },
        // Complex error
        {
            "code": 500,
            "type": "warn",
            "detail": "Message of the error",
            "_status_code": {
                "title": "Unauthorized",
                "style": {
                    "color": {
                        "text": "black",
                        "fill": "red"
                    },
                    "icon": {
                        "class": "material-icons",
                        "text": "block"
                    }
                }
            }
        }
    ],
    // Resulats of the query
    "results": {
        // Entity name
        "project": {
            // Records
            "records" : [ 
                {
                    "id": 1,
                    "entity": "project",
                    "attributes": {
                        "name": "Nom",
                        "description": "Description"
                    },
                    "relationships": {
                        "equipe": {
                            "records": [
                                {
                                    "id": 1,
                                    "entity": "equipe",
                                    "attributes": {
                                        "name": "Nom",
                                        "description": "Description"
                                    },
                                    "relationships": {}
                                }
                            ]
                        }
                    }
                }
            ],
            // Errors
            "_metadata": {
                "page": 1,
                "pagination": 25,
                "page_count": 10,
                "page_offset": 25,
                "records_count": 10,
                "records_total": 200,
                "Links": {
                    "self": "/project?page=5&per_page=20",
                    "first": "/project?page=0&per_page=20",
                    "previous": "/project?page=4&per_page=20",
                    "next": "/project?page=6&per_page=20",
                    "last": "/project?page=26&per_page=20",
                }
            },
            // Query
            "_query" : { }
        }
    },
    // Errors
    "_user_interface": {
        /* Only for first load */
        "structure": {
            "doctype": "<!DOCTYPE html>",
            "html": {
                "attributes": {
                    "lang": "fr",
                    "elements": {
                        "head": {
                            "attributes": {/* ... */},
                            "elements": {/* ... */}
                        },
                        "body": {
                            "attributes": {/* ... */},
                            "elements": {/* ... */}
                        },
                    }
                }
            }
        }
    },
    // Config
    "_config": {
        "app":{

        },
    },
    // Cookie
    "_cookies": {

    },
    // Context
    "_context":{
        "route":{
            "current": "/",
            "name": "Home",
            "patterns" :[
                "/index/"
            ],
            "methods":[
                "get"
            ],
            "response": "html",
            "Content-Type": "text\/html"
        }
    },
    // Events
    "_events": [
        // Type redirect ( ts type : `LoaderEventRedirection` )
        {
            "type":     "redirect",
            "name":     "url",      // Name of the internal page
            "url":      "url",      // Url of the external page
            "target":   "_blank"    // If `_blan  open the link in a new page`
        }
    ]
}
```
