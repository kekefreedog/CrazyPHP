# Update `.gitignore`

If you want ignore folder already commited. Here 3 steps to execute for fix issue :

- [ ] Remove git cache using command below :
    ```sh
    git rm -r --cached .
    ```

- [ ] Do a empty action using command below :
    ```sh
    git add .
    ```

- [ ] Do a commit using command below :
    ```sh
    git commit -m ".gitignore file updated"
    ```

And voilà 🎉