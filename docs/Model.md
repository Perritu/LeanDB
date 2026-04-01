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
}
```

@@TODO
