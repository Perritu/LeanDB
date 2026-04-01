# The `LeanDB\Model` class

(PHP 8.5.0+, Perritu\LeanDB v1.0.0+)

Abstract base class for models.

## Introduction

Base class for models. Allow the creation of subclasses that handles data operations in the database.

## Class synopsis

```php
namespace Perritu\LeanDB;

abstract class Model
{
  /* Constants */
  public const CONNECTION = null;
  public const TABLE = null;
  public const PERMS = LeanDB::PERM_NONE;
  public const FIELDS = [];
  public const SOFT_DELETES = true;
  public const TIMESTAMPS = true;

  /* Methods */
  public static function Create(array $aFields): PDOStatement;
  public static function Read(array $aCriteria = []): PDOStatement;
  public static function Update(array $aFields, array $aCriteria = []): PDOStatement;
  public static function Delete(array $aCriteria = []): PDOStatement;
}
```

## Constants

- `CONNECTION`
  - A path to a connection class that extends `Perritu\LeanDB\Connection`. Unless you have a very specific reason, this
    should **not** be left as `null`.
- `TABLE`
  - The table name. If left as `null`, the class name will be used as the table name.
- `PERMS`
  - The permissions for the model. It is a combination of `LeanDB::PERM_*` constants.
- `FIELDS`
  - An array of fields. See the definition below.
- `SOFT_DELETES`
  - A boolean value that indicates if soft deletes are enabled for the model.
- `TIMESTAMPS`
  - A boolean value that indicates if timestamps are enabled for the model.

### Fields array definitions

The `FIELDS` array is used to define the columns for the model. Each element of the array is an array that follows the
following format:

```php
class MyModel extends Model
{
  public const FIELDS = [
    'Field name' => [
      FieldType | FieldFlags,
      FieldLength,
      FieldDefault,
      FieldDescription,
    ],
    ...
  ];
}
```

In this format, `FieldType` is a combination of a `LeanDB::TYPES` constant any applicable `LeanDB::FLAGS` constants.

See the `LeanDB` -> Constants -> **LeanDB::TYPES** and **LeanDB::FLAGS** for more information.

## Methods

- `Create(array $aFields)`
  - Inserts a record into the database.
- `Read(array $aCriteria = [])`
  - Fetches a record from the database.
- `Update(array $aFields, array $aCriteria = [])`
  - Modifies a record in the database.
- `Delete(array $aCriteria = [])`
  - Removes a record from the database.
