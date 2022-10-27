# Context

> Context defined specific collection about the current root.

## Exemple of structure

```php
[__CRAZY_CONTEXT] => Array
        (
            [ROUTES] => Array
                (
                    [CURRENT] => Array
                        (
                            [NAME] => Favicon
                            [CONTROLLER] => App\Controller\Assets\Favicon
                            [PATTERNS] => Array
                                (
                                    [0] => /favicon/[s:favicon]
                                )

                            [METHODS] => Array
                                (
                                    [0] => get
                                )

                            [MIDDLEWARE] => Array
                                (
                                )

                            [PREFIX] => asset
                            [GROUP] => asset
                            [ROUTE] => asset/favicon/android-chrome-192x192.png
                            [PARAMETERS] => Array
                                (
                                    [FAVICON] => android-chrome-192x192.png
                                )

                        )

                )

        )
```