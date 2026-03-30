<?php

namespace Perritu\LeanDB;

/**
 * Root class. Handles common static methods and constants
 */
class LeanDB
{
  //! Constants for data types
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

  //! Constants for field flags
  public const NULLABLE = 16;
  public const UNIQUE   = 32;
  public const INDEX    = 64;
  public const AUTO     = 128;
  public const PKEY     = 96; // Unique index -> Primary key

  //! Field shortcuts
  public const ID = self::UINT | self::PKEY | self::AUTO;
  public const FK = self::UINT | self::INDEX;

  //! Constants for model permissions
  public const PERM_NONE   = 0;
  public const PERM_CREATE = 1;
  public const PERM_READ   = 2;
  public const PERM_UPDATE = 4;
  public const PERM_DELETE = 8;
  public const PERM_ALL    = 15;
}
