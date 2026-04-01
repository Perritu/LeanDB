<?php

namespace Perritu\LeanDB;

use PDOStatement;
use Perritu\LeanDB\LeanDB;

/**
 * The model class.
 *
 * Abstract base class for models. Extends this class to create models.
 */
abstract class Model
{
  /**
   * @var string Path to the connection class.
   */
  public const CONNECTION = null;

  /**
   * @var string The table name associated with the model. Use `null` to inherit the name from the class name.
   */
  public const TABLE = null;

  /**
   * @var int Model permissions.
   */
  public const PERMS = LeanDB::PERM_NONE;

  /**
   * @var array Model fields.
   */
  public const FIELDS = [];

  /**
   * @var bool Enable soft deletes.
   */
  public const SOFT_DELETES = true;

  /**
   * @var bool Enable timestamps.
   */
  public const TIMESTAMPS = true;

  /**
   * Insert data into the database.
   *
   * @param array[key=>value]|array[array[key=>value]] $aFields
   * @return PDOStatement
   */
  public static function Create(array $aFields): PDOStatement
  {
    $oConnection = static::CONNECTION;
    if (
      $oConnection === null ||
      (static::PERMS & LeanDB::PERM_CREATE) === 0
    ) {
      $cClass = static::class;
      throw new \Exception("The model class has no create permission: {$cClass}");
    }

    $cTable = static::TABLE;
    if (is_null($cTable)) {
      $cTable = array_reverse(explode('\\', static::class))[0];
    }

    [$cInsertSql, $aArgs] = LeanDB::BuildValues($aFields);
    $cQuery = "INSERT INTO `{$cTable}` \n {$cInsertSql}";
    $oPdoS = $oConnection::GetPDO()->prepare($cQuery);
    $oPdoS->execute($aArgs);
    return $oPdoS;
  }

  /**
   * Fetch data from the database.
   *
   * @param array $aCriteria
   * @return PDOStatement
   */
  public static function Read(array $aCriteria = []): PDOStatement
  {
    $oConnection = static::CONNECTION;

    if (
      $oConnection === null ||
      (static::PERMS & LeanDB::PERM_READ) === 0
    ) {
      $cClass = static::class;
      throw new \Exception("The model class has no read permission: {$cClass}");
    }

    [$cWhere, $aParams] = LeanDB::BuildWhere($aCriteria);

    $cTable = static::TABLE;
    if (is_null($cTable)) {
      $cTable = array_reverse(explode('\\', static::class))[0];
    }

    $cQuery = "SELECT * FROM `{$cTable}` WHERE {$cWhere}";
    $oPdoS = $oConnection::GetPDO()->prepare($cQuery);
    $oPdoS->execute($aParams);
    return $oPdoS;
  }
}
