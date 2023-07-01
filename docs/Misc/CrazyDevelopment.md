# Crasy Development

To setup a crazy project for debug CrazyPhp :

- [ ] copy the below content into the `composer.json` at the root of your project test :

    ```json
    {
        "require": {
            "kzarshenas/crazyphp": "@dev"
        },
        "repositories": [
            {
                "type": "path",
                "url": "../CrazyPHP",
                "options": {
                    "symlink": true
                }
            }
        ]
    }
    ```

    > Of course replace `../CrazyPHP` by the location of your CrazyPHP folder

- [ ] Execute the below command to install package :
    ```sh
    composer i
    ```

- [ ] Execute the below command to install node dependancies :
    ```sh
    npm i
    ```