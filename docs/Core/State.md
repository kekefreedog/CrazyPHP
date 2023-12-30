# State

State is a object that represent differents inputs for process pages. Exemple form schema, records from database...

## How retrieve state ?

Into the script of your page, use the function below to retrieve state :

`this.getPageState()`

## How retrieve from api

Here the argument that allow you to retrieve state of a given page :
`${url}?catch_state=true`