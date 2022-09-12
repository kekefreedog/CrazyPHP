# ***Api*** **Schema of response**

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
        "framework" : {
            "html": {
                "attributes": {
                    "class": "loading",
                    "data-textdirection": "ltr"
                }
            },
            "body": {
                "attributes": {
                    "class": [
                        "vertical-layout",
                        "page-header-light",
                        "vertical-menu-collapsible",
                        "preload-transitions",
                        "2-columns"
                    ],
                    "data-open": "click",
                    "data-menu": "vertical-dark-menu",
                    "data-col": "2-columns"
                }
            }
        },
        "popup": {
            "header": {
                "logo": {
                    "img": null,
                    "color": null
                },
                "content": {
                    "icon": {
                        "class": null,
                        "text": null
                    },
                    "text": null,
                    "description": null
                },
                "actions": {
                    "refresh": true,
                    "options": true,
                    "close": true
                }
            }
        },
        "action" : [
            {
                "type": "update",
                "target": [
                    "#id.class", "span"
                ]
            }
        ]
    },
    // Errors
    "_config": {
        "app":{

        },
    },
    // Cookie
    "_cookies": {

    },
    // Errors
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
    }
}
```
