# The Perritu\LeanDB\Connection class

(PHP 8.5.0+, Perritu\LeanDB v1.0.0+)

## Introduction

The `LeanDB\Connection` class wraps the `PDO` instance and its credentials.

## Class synopsis

```php
namespace Perritu\LeanDB;

abstract class Connection
{
  /* Properties */
  private static $aPdo = [];

  /* Constants */
  protected const CREDENTIALS = null;

  /* Methods */
  protected static function GetCredentials(): string;
  public static function GetPDO(): PDO;
  public static function Query(string $cQuery, array $aParams = []): PDOStatement;
}
```

## Properties

- `LeanDB\Connection::$aPdo`: An array of `PDO` instances. Stores the `PDO` instances for each connection class.

## Constants

- `LeanDB\Connection::CREDENTIALS`: A string that contains the credentials for the database connection.
  - For better security, left this constant null and override the `GetCredentials` method instead.

## Methods

- `LeanDB\Connection::GetCredentials(): string`
  - Returns the credentials for the database connection.
- `LeanDB\Connection::GetPDO(): PDO`
  - Returns the `PDO` instance for the database connection.
- `LeanDB\Connection::Query(string $cQuery, array $aParams = []): PDOStatement`
  - Executes a query on the database.
