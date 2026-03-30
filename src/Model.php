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
   * Fetch data from the database.
   *
   * @param array $aCriteria
   * @return PDOStatement
   */
  public static function Read(array $aCriteria = []): PDOStatement
  {
    $oConnection = static::CONNECTION;
    [$cWhere, $aParams] = LeanDB::buildWhere($aCriteria);

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
