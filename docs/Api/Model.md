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
