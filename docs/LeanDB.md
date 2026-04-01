# The Perritu\LeanDB class

(PHP 8.5.0+, Perritu\LeanDB v1.0.0+)

## Introduction

The `LeanDB` class is a static class that provides a simple interface to interact with the database.

## Class synopsis

```php
namespace Perritu\LeanDB;

class LeanDB
{
  /* Constants */
  // Constants for data types
  public const INT       = 0;
  public const UINT      = 1;
  public const DOUBLE    = 2;
  public const UDOUBLE   = 3;
  public const DEC       = 4;
  public const UDEC      = 5;
  public const CHAR      = 6;
  public const VARCHAR   = 7;
  public const TEXT      = 8;
  public const BINARY    = 9;
  public const BLOB      = 10;
  public const DATE      = 11;
  public const TIME      = 12;
  public const DATETIME  = 13;
  public const TIMESTAMP = 14;
  public const RAWSQL    = 15;

  // Constants for field flags
  public const NULLABLE = 16;
  public const UNIQUE   = 32;
  public const INDEX    = 64;
  public const AUTO     = 128;
  public const PKEY     = 96; // Unique index -> Primary key

  // Field shortcuts
  public const ID = self::UINT | self::PKEY | self::AUTO;
  public const FK = self::UINT | self::INDEX;

  // Constants for model permissions
  public const PERM_NONE   = 0;
  public const PERM_CREATE = 1;
  public const PERM_READ   = 2;
  public const PERM_UPDATE = 4;
  public const PERM_DELETE = 8;
  public const PERM_ALL    = 15;

  /* Methods */
  public static function BuildSchema($cClass): string;
  public static function BuildWhere(array $aCriteria, string $cOperator = 'AND'): array;
  public static function BuildSet(array $aFields): array;
  public static function BuildValues(array $aFields): array;
}
```

## Constants

- **LeanDB TYPES**: Constants for data types.
  - `LeanDB::INT`: Integer.
  - `LeanDB::UINT`: Unsigned integer.
  - `LeanDB::DOUBLE`: Double.
  - `LeanDB::UDOUBLE`: Unsigned double.
  - `LeanDB::DEC`: Decimal.
  - `LeanDB::UDEC`: Unsigned decimal.
  - `LeanDB::CHAR`: Character string.
  - `LeanDB::VARCHAR`: Variable character string.
  - `LeanDB::TEXT`: Long character string.
  - `LeanDB::BINARY`: Binary data.
  - `LeanDB::BLOB`: Large binary data.
  - `LeanDB::DATE`: Date.
  - `LeanDB::TIME`: Time.
  - `LeanDB::DATETIME`: Date and time.
  - `LeanDB::TIMESTAMP`: Timestamp.
  - `LeanDB::RAWSQL`: Raw SQL statement.

Each type (except `LeanDB::RAWSQL`) follows the definition array `[LeanDB::TYPE, Length, Default, Description]`.

For `LeanDB::RAWSQL`, the definition array is `[LeanDB::RAWSQL, Raw SQL]`.

Each type (except `LeanDB::RAWSQL`) can be combined with the following flags:

- **LeanDB FLAGS**: Constants for field flags.
  - `LeanDB::NULLABLE`: Set the field to allow `NULL` values.
  - `LeanDB::UNIQUE`: Set the field to be unique across the table.
  - `LeanDB::INDEX`: Set the field to be indexed.
  - `LeanDB::AUTO`: Set the field to auto-increment. Useful for primary keys.
  - `LeanDB::PKEY`: Set the field to be a primary key. Not assumes `LeanDB::AUTO`.

For convenience, the following shortcuts are defined:

- `LeanDB::ID`: An unsigned integer auto-incremented primary key. `LeanDB::UINT | LeanDB::PKEY | LeanDB::AUTO`.
- `LeanDB::FK`: An indexed unsigned integer. `LeanDB::UINT | LeanDB::INDEX`.

## Methods

- `LeanDB::BuildSchema($cClass): string`
  - Builds the schema for a model.
- `LeanDB::BuildWhere(array $aCriteria, string $cOperator = 'AND'): array`
  - Builds a SQL `WHERE` clause.
- `LeanDB::BuildSet(array $aFields): array`
  - Builds a SQL `SET` clause.
- `LeanDB::BuildValues(array $aFields): array`
  - Builds a SQL `VALUES` clause.
