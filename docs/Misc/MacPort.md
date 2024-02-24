# MacPort

## Upgrade PHP version

1. Install new php version 
    ```sh
    sudo port install php83
    ```

2. Check php version installed
    ```sh
    sudo port select --list php
    ```

3. Select php version
    ```sh
    sudo port select --set php php83
    ```

4. Install modules
    ```sh
    # Iconv
    sudo port install php83-iconv
    # Mbstring
    sudo port install php83-mbstring
    # Openssl
    sudo port install php83-openssl
    # Curl
    sudo port install php83-curl
    ```

And voilà 🎉