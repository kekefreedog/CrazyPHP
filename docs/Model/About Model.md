# About Model

- [x] Model allow interaction with collection of data. 
- [x] Differents drivers allow a communication between model and Array, Mongo DB...
- [x] Then model is very useful for automated API of your Crazy App.

## Config

In config Model you define all options about your Model (Name, Labels, Attributes)

Using the type you can automatically let API dynamically process exchange with data. Or overwrite methods to have a full customizablable model.

```yml
Model:
  - name: Router
    type: config
    attributes: 
      - name: name
        label: Name
        description: Name of the router
        type: VARCHAR
        default: null
        required: true
        process: null
        validate: null
      - name: path
        label: Path
        description: Path of the front controller
        type: VARCHAR
        validate: [isValidUrl]
        required: false
```

## How to use it ?

First import this class in your script :
```php
use CrazyPHP\Core\Model;
```

Then create a new instance, in argument you can write name of your model. Or let script automaticcaly found the good model in case it is used for extends custom model Class.

```php
$modelInstance = new Model("Router");
```

## Create

Create method allow user to create new item

```php
$createdItem = $modelInstance->create($data, $options);
```

