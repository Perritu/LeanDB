<?php

namespace Perritu\LeanDB;

use PDO;
use PDOStatement;
use Uri\Rfc3986\Uri;

/**
 * The connection class.
 *
 * Abstract base class for connections. Extend this class and use such as a connection for models.
 * !! Don't use this class directly !!
 */
abstract class Connection
{
  /** @var array Static connections cache */
  private static $aPdo = [];

  /** @var string|null Database URI credentials */
  protected const CREDENTIALS = null;

  /**
   * Return the credentials string. Can be overridden to fetch credentials from a config file or environment variables.
   *
   * @return string
   */
  protected static function GetCredentials(): string
  {
    if (static::CREDENTIALS !== null) {
      return static::CREDENTIALS;
    }

    $cClassName = static::class;
    throw new \Exception("The connection class has no valid credentials: {$cClassName}");
  }

  /**
   * Return the connection object.
   *
   * @return PDO
   */
  public static function &GetPDO(): PDO
  {
    if (!isset(self::$aPdo[static::class])) {
      $oCredentials = new Uri(static::GetCredentials());
      $cUser        = $oCredentials->getUsername();
      $cPassword    = $oCredentials->getPassword();
      $cHost        = $oCredentials->getHost();
      $cPort        = $oCredentials->getPort();
      $cDatabase    = substr($oCredentials->getPath(), 1);

      self::$aPdo[static::class] = new PDO(
        "mysql:host={$cHost};port={$cPort};dbname={$cDatabase}",
        $cUser,
        $cPassword
      );
    }

    return self::$aPdo[static::class];
  }

  /**
   * Perform a query.
   *
   * @param string $cQuery
   * @param array $aParams
   * @return PDOStatement
   */
  public static function Query(string $cQuery, array $aParams = []): PDOStatement
  {
    $oPDOs = static::GetPDO()->prepare($cQuery);
    $oPDOs->execute($aParams);
    return $oPDOs;
  }
}
