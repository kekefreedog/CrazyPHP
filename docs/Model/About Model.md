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
      - name: name                                    # Name of the attribute
        label: Name                                   # Label used on the UI
        description: Name of the router               # Description used on the UI
        type: VARCHAR                                 # Type of the attributes INT|VARCHAR|ARRAYBOOL|FILE
        default: null                                 # Define the default value, used in case the value given is null 
        required: true                                # Define if the value is required
        process: null                                 # Define the process to apply to the attributes null|array
        validate: null                                # Define the validate to apply to the attributes null|array
        select:                                       # In case you want generate a select form, you can define the value -> label to do it
          value01: label01
          value02: label02
        multiple: false                               # Define if multiple value allowed in the attributes
        extAllow:                                     # In case of FILE, define the extension allowed
        extOmit:                                      # In case of FILE, define the extension to omit
        source:                                       # Can be use in case you want retrieve value from another model (if key01 is find before key02, it will stop at the first found except if multiple is selected)
          - key01
          - key02
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

