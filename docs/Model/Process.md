# Process

> List of process proposed by default by Crazy PHP

Process are functions that transform value given to be sure their respects some rules.

## Trim <*string*>

Remove empty space both side of the string givne

Exemple :
```php
trim(" Hello World ! ") = "Hello World !"
```

## Clean <*string*>

Clean the strings to be used as attribute value or page url

Exemple :
```php
clean("Bienvenue à Montréal ! ") = "bienvenue_a_montreal"
```


## Clean Path <*string*>

Alternative to clean function but keep "/"

Exemple :
```php
clean(" Woo c'est ouf ! ") = "woo_c_est_ouf"
```

## Https <*string*>

Add or replace `http` by `https`

Exemple :
```php
https("http://www.google.com") = "https://www.google.com"
```

## Bool <*bool*>

Process value as bool

Exemple :
```php
bool("true"|"1"|true) = true
```

## Email <*string*>

Check if string given is email, else return empty string

Exemple :
```php
email("Not email") = ""
```

## Camel To Snake <*string*>

Convert string from camel to snake

Exemple :
```php
camelToSnake("HelloWorld") = "hello_world"
```

## Camel To Path <*string*>

Convert string from camel to url

Exemple :
```php
camelToPath("HelloWorldTest") = "Hello/World/Test"
camelToPath("HelloWorldTest", true) = "hello/world/test"
camelToPath("HelloWorldTest", true, "\\") = "hello\\world\\test"
```

## Snake To Camel <*string*>

Convert string from snake to camel

Exemple :
```php
snakeToCamel("hello_tout_le_monde") = "helloToutLeMonde"
snakeToCamel("hello_tout_le_monde", true) = "HelloToutLeMonde"
```

## Space Before Capital <*string*>

Insert space before capital letter (except if it is the first character)

Exemple :
```php
spaceBeforeCapital("HelloWorld") = "Hello World"
```

## Upper Case First <*string*>

Uper Case on the first value of the string

Exemple :
```php
ucfirst("hello World") = "Hello world"
```

