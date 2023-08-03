# Migration

## About

Migration is used for update file on your app to be compatible with the last version of CrazyPHP.

Those migration actions are rule by a yml file avaialble here :
```
resources/Yml/Migration.yml
```

## Actions

### String Replace

Replace string by another string.

*Exemple*

You want replace `window.Crazyobject.pages.register(` by `window.Crazyobject.register(` in all ts file in the folder `@app_root/app/Environment` and then noticed that this action required to rebuild the front script

Here the action you can use :
```yml
[...]
-   name: upgradePageRegisterCommand
    description: Upgrade command that register pages
    action: 
        type: string_replace
        from: window.Crazyobject.pages.register(
        to: window.Crazyobject.register(
        name: "*.ts"
        in: "@app_root/app/Environment"
    frontBuildRequired: true
[...]
``