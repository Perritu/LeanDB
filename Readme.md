# Perritu/LeanDB

A simple and light yet powerful ORM for PHP.

## Installation

The best way to install `LeanDB` is to use [Composer](https://getcomposer.org/):

```bash
composer require perritu/leandb
```

Direct installation can be done, but it's not yet tested.

## Usage

```php
use Perritu\LeanDB\LeanDB;
use Perritu\LeanDB\Connection;
use Perritu\LeanDB\Model;

class MysqlCnn extends Connection
{
  protected static function GetCredentials(): string
  {
    $cUser     = getenv('MYSQL_USER');
    $cPassword = getenv('MYSQL_PASSWORD');
    $cHost     = getenv('MYSQL_HOST');
    $cPort     = getenv('MYSQL_PORT');
    $cDatabase = getenv('MYSQL_DATABASE');
    return "mysql://{$cUser}:{$cPassword}@{$cHost}:{$cPort}/{$cDatabase}";
  }
}

class User extends Model
{
  public const CONNECTION = MysqlCnn::class;
  public const TABLE = 'Users';
  public const FIELDS = [
    'User'     => [LeanDB::ID, null, null, 'User internal ID'],
    'Name'     => [LeanDB::VARCHAR, 30, null, 'User name'],
    'Email'    => [LeanDB::VARCHAR, 100, null, 'User email'],
    'Password' => [LeanDB::CHAR, 64, null, 'User password hash'],
    'Salt'     => [LeanDB::CHAR, 64, null, 'User password salt'],
  ];
  public const PERMS = LeanDB::PERM_ALL;
  public const SOFT_DELETES = true;
  public const TIMESTAMPS = true;
}

User::Create([
  'Name'     => 'John Doe',
  'Email'    => 'git@john.doe',
  'Password' => '4b57da426cca84945d79c2afa37635f0171674264ab03bfd81852e7342a70c56',
  'Salt'     => '0xd488d6befe35b869ccc4be07587846810cc175d488d6befe35b869ccc4be07',
])

$oPdoS = User::Read(['Email' => 'git@john.doe']);
if ($aUser = $oPdoS->fetch()) {
  $cExpectedPassword = $aUser['Password'];
  $cSalt = $aUser['Salt'];
  if (SecretPasswordGestor::Verify($cExpectedPassword, $cSalt)) {
    // ...
  }
}
```
