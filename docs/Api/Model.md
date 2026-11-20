# Model Class Documentation

## Overview

This class is part of the `CrazyPHP\Core` namespace and serves as the main model management component for the application. 

## Table of Contents
- [Model Class Documentation](#model-class-documentation)
  - [Overview](#overview)
  - [Table of Contents](#table-of-contents)
  - [Properties](#properties)
  - [Methods](#methods)
    - [Constructor](#constructor)
    - [Create Methods](#create-methods)
    - [Id Methods](#id-methods)
    - [Filter Methods](#filter-methods)
    - [SQL Methods](#sql-methods)
    - [Utilities](#utilities)
    - [Static Methods](#static-methods)

---

## Properties

- `$name` - (private, string) Name of the current entity called.
- `$current` - (private, array|null) Current model.
- `$driver` - (private, CrazyDriverModel) An instance of the model's driver.

---

## Methods

### Constructor

- `__construct(string $entity = "")`: Initializes the model.

### Create Methods

- `create(array $data, ?array $options = null): array`: Creates a new item.

### Id Methods

- `readById(string|int $id, ?array $options = null): array`: Reads an item by its ID.
- `updateById(string|int $id, array $data, ?array $options = null): array`: Updates an item by its ID.
- `deleteById(string|int $id, ?array $options = null): array`: Deletes an item by its ID.

### Filter Methods

- `readAttributes(?array $options = null): self`: Reads attributes as values of the model.
- `readWithFilters(?array $filters = null, null|array|string $sort = null, ?array $group = null, ?array $options = null): array`: Reads items based on filters, sort options, and grouping.
- `countWithFilters(?array $filters = null, ?array $options = null): int`: Counts items based on filters.
- `updateWithFilters(array $data, ?array $filters = null, ?array $options = null): array`: Updates items based on filters.
- `deleteWithFilters(array $filters, ?array $options = null): array`: Deletes items based on filters.

### SQL Methods

- `createWithSql(string $sql, array $data, ?array $options = null): array`: Creates an item using a raw SQL query.
- `readWithSql(string $sql, ?array $options = null): array`: Reads items using a raw SQL query.
- `updateWithSql(string $sql, array $data, ?array $options = null): array`: Updates items using a raw SQL query.
- `deleteWithSql(string $sql, ?array $options = null): array`: Deletes items using a raw SQL query.

### Utilities

- `getCurrent(): array|null`: Gets the current model.

### Static Methods

- `getListAllModel(): array`: Retrieves a list of all models.


## SQL filter execution

The `Mariadb` and `Sql` model drivers pass original filter values to a shared SQL compiler. The MariaDB and MySQL database drivers execute these filters with PDO placeholders and bound values. Filter fields and sort fields must be simple model identifiers; SQL expressions, comments, quoted identifiers and nested filter objects are rejected with a 400 error.

Supported forms include literal equality, flat `IN` lists, numeric comparisons, ranges, wildcard matching, `||`/`!!` OR prefixes, and `<>`/`@@`/`><` model relations. An empty `IN` list matches no records. Null means `IS NULL`; zero, false and empty strings remain literal values. The historical `=column` and `!=column` forms compare columns: use an unprefixed value or a one-element list for literal equality. Sorting accepts `field`, `-field`, `asc` and `desc` (the last two sort by ID). Relation constraint values are bound as literals.

For low-level integrations, `SqlOperation::run()` now returns an array with `sql` and `parameters` entries rather than an executable SQL string. Pass original model DSL values to database-driver `find()`; do not precompile or concatenate them. All framework callers have been updated. The explicit server-side `parseSql()`/raw-query API remains trusted code and must never receive caller-supplied SQL.

`SqlOperationTest`, `SqlFilterTest` and `MariadbTest` run without a database. `SqlFilterIntegrationTest` requires `CRAZY_SQL_TEST_DSN` pointing to a disposable MariaDB server and optional `CRAZY_SQL_TEST_USER`/`CRAZY_SQL_TEST_PASSWORD`. It creates and removes randomly named fixture databases and never reads application database credentials. Without that explicit DSN, integration cases are skipped.
