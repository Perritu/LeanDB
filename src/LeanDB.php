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

  /**
   * Build a model schema from a class.
   *
   * @param string $cClass
   * @return string The schema in SQL format
   */
  public static function BuildSchema($cClass)
  {
    // General variables.
    $aFields = $cClass::FIELDS;
    $cTable  = $cClass::TABLE;
    $bSoftDeletes = $cClass::SOFT_DELETES;
    $bTimestamps = $cClass::TIMESTAMPS;

    // Fallback for table name.
    if (is_null($cTable)) {
      $cTable = array_reverse(explode('\\', $cClass))[0];
    }

    // Timestamps and soft deletes.
    if ($bSoftDeletes) {
      $aFields['__deleted_at'] = [LeanDB::DATETIME | LeanDB::NULLABLE, null, null, 'Deletion stamp'];
    }

    if ($bTimestamps) {
      $aFields['__created_at'] = [LeanDB::DATETIME, null, ['NOW()'], 'Creation stamp'];
      $aFields['__updated_at'] = [LeanDB::DATETIME, null, ['NOW() ON UPDATE NOW()'], 'Last update stamp'];
    }

    $aSqlFields = [];
    foreach ($aFields as $cName => $mField) {
      if (is_array($mField)) {
        $iFieldType   = $mField[0] & 15;  // first 4 bits
        $iFieldFlags  = $mField[0] & 240; // last 4 bits
        $iFieldLength = $mField[1] ?? null;
        $aDefault     = $mField[2] ?? null;
        $cComment     = $mField[3] ?? null;
      } else {
        $iFieldType   = $mField & 15;  // first 4 bits
        $iFieldFlags  = $mField & 240; // last 4 bits
        $iFieldLength = null;
        $aDefault     = null;
        $cComment     = null;
      }

      // Raw SQL
      if ($iFieldType === LeanDB::RAWSQL) {
        $aSqlFields[] = "`{$cName}` {$mField[1]}";
        continue;
      }

      $iFieldLength = match ($iFieldType) {
        LeanDB::INT, LeanDB::UINT,
        LeanDB::DOUBLE, LeanDB::UDOUBLE,
        LeanDB::DEC, LeanDB::UDEC => $iFieldLength ?? 9,

        LeanDB::CHAR, LeanDB::VARCHAR, LeanDB::BINARY => $iFieldLength ?? 255,
        LeanDB::TEXT, LeanDB::BLOB => $iFieldLength ?? 65535,
        LeanDB::DATE, LeanDB::TIME, LeanDB::DATETIME, LeanDB::TIMESTAMP => null,
        default => $iFieldLength ?? null,
      };

      if ($iFieldType === LeanDB::INT || $iFieldType === LeanDB::UINT) {
        // The right type of int.
        switch (true) {
          case $iFieldLength <= 2:
            $cFieldType = 'TINYINT';
            break;
          case $iFieldLength > 2 && $iFieldLength <= 4:
            $cFieldType = 'SMALLINT';
            break;
          case $iFieldLength > 4 && $iFieldLength <= 7:
            $cFieldType = 'MEDIUMINT';
            break;
          case $iFieldLength > 7 && $iFieldLength <= 9:
            $cFieldType = 'INT';
            break;
          case $iFieldLength > 9:
            $cFieldType = 'BIGINT';
            break;
        }
      } else {
        $cFieldType = match ($iFieldType) {
          LeanDB::DOUBLE, LeanDB::UDOUBLE => 'DOUBLE',
          LeanDB::DEC, LeanDB::UDEC => 'DECIMAL',
          LeanDB::CHAR => 'CHAR',
          LeanDB::VARCHAR => 'VARCHAR',
          LeanDB::TEXT => 'TEXT',
          LeanDB::BINARY => 'BINARY',
          LeanDB::BLOB => 'BLOB',
          LeanDB::DATE => 'DATE',
          LeanDB::TIME => 'TIME',
          LeanDB::DATETIME => 'DATETIME',
          LeanDB::TIMESTAMP => 'TIMESTAMP',
          default => throw new \InvalidArgumentException("Unknown field type: {$iFieldType}"),
        };
      }

      // Add the field length.
      $cFieldType .= match ($iFieldType) {
        LeanDB::DOUBLE, LeanDB::DEC,
        LeanDB::CHAR, LeanDB::VARCHAR,
        LeanDB::TEXT, LeanDB::BINARY, LeanDB::BLOB => "({$iFieldLength})",
        LeanDB::UINT => " UNSIGNED",
        LeanDB::UDOUBLE, LeanDB::UDEC => "({$iFieldLength}) UNSIGNED",
        default => '',
      };

      // Compile the field flags.
      $bNullable = ($iFieldFlags & LeanDB::NULLABLE) === LeanDB::NULLABLE;
      $bPrimary  = ($iFieldFlags & LeanDB::PKEY) === LeanDB::PKEY;
      $bUnique   = ($iFieldFlags & LeanDB::UNIQUE) === LeanDB::UNIQUE && !$bPrimary;
      $bIndex    = ($iFieldFlags & LeanDB::INDEX) === LeanDB::INDEX && !$bPrimary;
      $bAuto     = ($iFieldFlags & LeanDB::AUTO) === LeanDB::AUTO;

      $aFlags = [];
      if (!$bNullable) $aFlags[] = 'NOT NULL';
      elseif ($aDefault === null) $aFlags[] = 'NULL';
      if ($bUnique) $aFlags[] = 'UNIQUE';
      if ($bIndex) $aFlags[] = 'INDEX';
      if ($bAuto) $aFlags[] = 'AUTO_INCREMENT';
      if ($bPrimary) $aFlags[] = 'PRIMARY KEY';

      if ($aDefault !== null) {
        if (is_array($aDefault)) {
          $aDefault = implode(' ', $aDefault);
          $aFlags[] = "DEFAULT {$aDefault}";
        } else {
          $aFlags[] = "DEFAULT '{$aDefault}'";
        }
      }

      if (is_string($cComment)) {
        $aFlags[] = "COMMENT '{$cComment}'";
      }

      // $cFlags = implode(' ', $aFlags);
      // $aSqlFields[] = "{$cName} {$cFieldType} {$cFlags}";
      $aSqlFields[] = implode(' ', [
        "`{$cName}`",
        "{$cFieldType}",
        ...$aFlags
      ]);
    }

    // Build the table definition.
    $cSqlFields = implode(",\n  ", $aSqlFields);

    return "CREATE TABLE IF NOT EXISTS `{$cTable}` (\n  {$cSqlFields}\n);";
  }
}
