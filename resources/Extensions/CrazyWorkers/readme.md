# Crazy woekers

## Worker config

### Timer

Here an exemple of **Timer** config to add into Workers.yml

```yml
Workers:
  list:
    [...]
    -   # Worker name
        name: "timerName"
        type: "timer"
        class: "@app_root/app/Library/Workers/timerName.php"
        arguments:
            duration:   300 # 5 minutes in seconds  
            interval:   10  # Check every 10 seconds
        logger: true        
    [...]
```

### Command

Run :
```sh
php vendor/kzarshenas/crazyphp/bin/CrazyWorkers run
```