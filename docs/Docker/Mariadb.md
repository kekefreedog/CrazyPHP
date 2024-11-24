# MariaDB with Docker

## Start shell of MySQL

Use bellow command

```sh
docker-compose exec mysql bash 
```

## Connect to MySQL bash

1. Get container name of mysql

```sh
docker-compose ps 
# Result : crazytest-mariadb-1
```

## Explore your MariaDB database 

### 1. Connect to database as admin

```sh
docker exec -it crazytest-mariadb-1 mysql -p
```

### 2. Connect to MySql (Mariadb)

Execute the command below :

> You can found the password into the `docker-compose.yml`if you are using docker compose

```sh
mysql -u root -p
```

### 3. Show all databases

Execute the command below :

```sh
SHOW DATABASES;
```

Result :
```sh
MariaDB [(none)]> SHOW DATABASES;
+--------------------+
| Database           |
+--------------------+
| crazy_db           |
| information_schema |
| mysql              |
| performance_schema |
+--------------------+
```

### 4. Select database to explore

Execute the command below :

```sh
USE crazy_db;
```

Result :
```sh
Database changed
MariaDB [crazy_db]> 
```

### 5. Show all tables

Execute the command below :

```sh
SHOW TABLES;
```

Result :
```sh
MariaDB [crazy_db]> SHOW TABLES;
+--------------------+
| Tables_in_crazy_db |
+--------------------+
| Booking            |
+--------------------+
```

### 6. Delete table

Execute the command below :

```sh
DROP TABLE {table};
```

Result :
```sh
Query OK, 0 rows affected
```

### 5. Show all tables

Get schema of one table

```sql
SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='Booking';
```