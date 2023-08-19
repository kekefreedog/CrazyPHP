# Migration

## About

Migration is used for update file on your app to be compatible with the last version of CrazyPHP.

Those migration actions are rule by a yml file avaialble here :
```
resources/Yml/Migration.yml
```

> Please notice that files into `vendor` or `node_package` can't be updated via migration script

## Command

### Check

For check if some migrations are needed, here the command to execute on terminal in your project root :
```sh
php vendor/kzarshenas/crazyphp/bin/CrazyMigration check
```

### Run

For run migrations, here the command to execute on terminal in your project root :
```sh
php vendor/kzarshenas/crazyphp/bin/CrazyMigration run
```

## Actions

### String Replace

Replace string by another string.

*Exemple*

You want replace `window.Crazyobject.pages.register(` by `window.Crazyobject.register(` in all ts file in the folder `@app_root/app/Environment` and then noticed that this action required to rebuild the front script files.

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
```

### Add Line

Add line at the top or the end of file

*Exemple*

You want add lines bellow in the file `.gitignore`in `@app_root`
```txt
# Vscode
.vscode"
```

Here the action you can use :
```yml
[...]
-   name: upgradeGitignore
    description: Add some rules in .gitignore file
    action: 
        type: add_line
        add: |-
          # Vscode
          .vscode"
        position: end
        name: "*.gitignore"
        in: "@app_root"
    frontBuildRequired: true
[...]
```

### Reduce Path

Reduce absolute path to relative

*Exemple*

You want convert absolute path to relative path of the attribute `App.root` in `@app_root/config/App.yml` and then noticed that this action required to rebuild the docker files
```yml
App:
    # Before
    root: /Users/kzarshenas/Sites/CrazyProject/CrazyDev
    # After
    root: "."
```

Here the action you can use :
```yml
[...]
- name: relativePathOnConfigs
    description: Reduce path to relative to avoid project moving to another folder
    action: 
    type: reduce_path
    env: app_root
    in:
        - "@app_root/config/App.yml": App.root
        - "@app_root/config/App.yml": App.framework.path
    newDockerRequired: true
[...]
```