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

## Examples

### Using a direct credentials string

```php
class Database extends Connection
{
  protected const CREDENTIALS = 'mysql://username:password@localhost/database';
}
```

This way, the default method `GetCredentials` will return the credentials string. It's a good idea to override this
method in order to fetch credentials from a config file or environment variables.

```php
class Database extends Connection
{
  protected static function GetCredentials(): string
  {
    return 'mysql://username:password@localhost/database';
  }
}
```

Note that the method must return a valid URI string. This particular example returns a constant string, but you can
fetch it from a config file, environment variables or any other source available in your code flow.

### Performing a direct query

```php
class Database extends Connection
{
  // ...
}

$oPdoS = Database::Query('SELECT * FROM users');
```

This is a simple way to perform raw queries. The connection will instance the `PDO` (if not already), perform the
query and return the result as a `PDOStatement` object. This method also allows you to pass parameters to the query.

```php
class Database extends Connection
{
  // ...
}

$oPdoS = Database::Query('SELECT * FROM users WHERE username = ? AND password = ?', ['username', 'password']);
```

### Manipulating the `PDO` instance

The `LeanDB\Connection::GetPDO` method returns the `PDO` instance as a reference. This is useful when you need to set
attributes on the `PDO` instance.

```php
class Database extends Connection
{
  // ...
}

$oPdo = Database::GetPDO();
$oPdo->setAttribute(PDO::FETCH_MODE, PDO::FETCH_ASSOC);
```

This will cause any subsequent queries (including those used by any model using this connection) to use return their
results as an associative array.
