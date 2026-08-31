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
      $aFields['__deleted_at'] = [LeanDB::DATETIME | LeanDB::NULLABLE | LeanDB::INDEX, null, null, 'Deletion stamp'];
    }

    if ($bTimestamps) {
      $aFields['__created_at'] = [LeanDB::DATETIME, null, ['NOW()'], 'Creation stamp'];
      $aFields['__updated_at'] = [LeanDB::DATETIME, null, ['NOW() ON UPDATE NOW()'], 'Last update stamp'];
    }

    $aSqlFields  = [];
    $aSqlKeys    = [];
    $aPrimaryKey = [];
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
        if (is_int($cName)) $aSqlFields[] = $mField[1];
        else $aSqlFields[] = "`{$cName}` {$mField[1]}";
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
      $bUnique   = ($iFieldFlags & LeanDB::PKEY) === LeanDB::UNIQUE;
      $bIndex    = ($iFieldFlags & LeanDB::PKEY) === LeanDB::INDEX;
      $bAuto     = ($iFieldFlags & LeanDB::AUTO) === LeanDB::AUTO;

      $aFlags = [];
      $aKeys  = [];
      if (!$bNullable) $aFlags[] = 'NOT NULL';
      elseif ($aDefault === null) $aFlags[] = 'NULL';
      if ($bPrimary) $aPrimaryKey[] = "`{$cName}`";

      if ($bUnique) $aFlags[]  = 'UNIQUE';
      if ($bAuto) $aFlags[]    = 'AUTO_INCREMENT';
      if ($bIndex) $aKeys[]    = "INDEX (`{$cName}`)";

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

      $aSqlFields[] = implode(' ', [
        "`{$cName}`",
        "{$cFieldType}",
        ...$aFlags
      ]);

      if (count($aKeys) > 0) {
        $aSqlKeys[] = implode(' ', $aKeys);
      }
    }

    // Build the primary key.
    if (count($aPrimaryKey) > 0) {
      $aSqlKeys[] = "PRIMARY KEY (" . implode(', ', $aPrimaryKey) . ")";
    }

    // Build the table definition.
    $cSqlFields = implode(
      ",\n  ",
      [
        ...$aSqlFields,
        ...$aSqlKeys,
      ]
    );

    return "CREATE TABLE IF NOT EXISTS `{$cTable}` (\n  {$cSqlFields}\n);";
  }

  /**
   * Build a SQL `WHERE` clause.
   *
   * @param array $aCriteria
   * @param string $cOperator
   * @return array[string,array]
   */
  public static function BuildWhere(array $aCriteria, string $cOperator = 'AND'): array
  {
    $aWhere = [];
    $aArgs  = [];

    foreach ($aCriteria as $cField => $mValue) {
      // If the key is literal '*', then it's a raw query.
      if ($cField === '*' && is_string($mValue)) {
        $aWhere[] = $mValue;
        continue;
      }

      // Comparation modifiers.
      $bNegate = substr($cField, 0, 1) === '!';
      $bGeq    = substr($cField, -2) === '>=';
      $bLeq    = substr($cField, -2) === '<=';
      $bGt     = substr($cField, -1) === '>';
      $bLt     = substr($cField, -1) === '<';

      // Adjust the field name.
      if ($bNegate)       $cField = substr($cField, 1);
      if ($bGeq || $bLeq) $cField = substr($cField, 0, -2);
      if ($bGt  || $bLt)  $cField = substr($cField, 0, -1);

      if (is_array($mValue)) { // If the value is an array and the key...
        if (is_numeric($cField)) { // ... is numeric, then it's a subquery.
          $cSubOperator = match ($cOperator) {
            'AND' => 'OR',
            default => 'AND',
          };
          [$cSubSql, $aSubArgs] = static::buildWhere($mValue, $cSubOperator);
          $aSubSql = explode("\n", $cSubSql);
          foreach ($aSubSql as &$cLine) $cLine = "  {$cLine}";
          $aWhere[] = implode("\n", [
            '(',
            ...$aSubSql,
            ')',
          ]);
          $aArgs = array_merge($aArgs, $aSubArgs);
        } else { // ... is non numeric, then it's a IN clause.
          $cFiller = implode(', ', array_fill(0, count($mValue), '?'));
          $cSubOperator = $bNegate ? 'NOT IN' : 'IN';
          $aWhere[] = "`{$cField}` {$cSubOperator} ({$cFiller})";
          $aArgs = array_merge($aArgs, array_values($mValue));
        }
        continue;
      }

      // If the value is null, then it's a NULL clause.
      if ($mValue === null) {
        $cSubOperator = $bNegate ? 'IS NOT NULL' : 'IS NULL';
        $aWhere[] = "`{$cField}` {$cSubOperator}";
        continue;
      }

      // Any other value.
      if (($bGeq && $bNegate) || ($bLt && !$bNegate))     $aWhere[] = "`{$cField}` < ?";
      elseif (($bLeq && $bNegate) || ($bGt && !$bNegate)) $aWhere[] = "`{$cField}` > ?";
      elseif (($bGt && $bNegate) || ($bGeq && !$bNegate)) $aWhere[] = "`{$cField}` >= ?";
      elseif (($bLt && $bNegate) || ($bLeq && !$bNegate)) $aWhere[] = "`{$cField}` <= ?";
      elseif ($bNegate)                                   $aWhere[] = "`{$cField}` != ?";
      else                                                $aWhere[] = "`{$cField}` = ?";
      $aArgs[] = $mValue;
    }

    return [
      implode(" {$cOperator}\n", $aWhere),
      $aArgs,
    ];
  }

  /**
   * Build a SQL set of fields for use in `UPDATE`.
   *
   * @param array[key=>value] $aFields
   * @return array[string,array]
   */
  public static function BuildSet(array $aFields): array
  {
    if (count($aFields) === 0) return ['', []];
    if (!is_array($aFields[0] ?? null)) $aFields = [$aFields];
    $aRows = [];
    $aArgs = [];

    foreach ($aFields as $aRow) {
      $aSet = [];
      foreach ($aRow as $cField => $mValue) {
        $aSet[] = "`{$cField}` = ?";
        $aArgs[] = $mValue;
      }
      $aRows[] = implode(', ', $aSet);
    }

    return [
      implode(",\n", $aRows),
      $aArgs,
    ];
  }

  /**
   * Build a SQL set of fields for use in `INSERT`.
   *
   * @param array[key=>value]|array[array[key=>value]] $aFields
   * @return array[string,array]
   */
  public static function BuildValues(array $aFields): array
  {
    if (count($aFields) === 0) return ['', []];
    if (!is_array($aFields[0] ?? null)) $aFields = [$aFields];

    $aHeaders = [];
    foreach ($aFields as $aRow) {
      foreach ($aRow as $cField => $mValue) {
        $aHeaders[$cField] = true;
      }
    }
    $aHeaders = array_keys($aHeaders);
    $aRows = [];
    $aArgs = [];

    foreach ($aFields as $aRow) {
      $aRowSQL  = [];
      $aRowArgs = [];
      foreach ($aHeaders as $cKey) {
        $mValue = $aRow[$cKey] ?? null;

        if (is_string($mValue) || is_numeric($mValue)) {
          $aRowSQL[] = '?';
          $aRowArgs[] = $mValue;
          continue;
        }

        if (is_bool($mValue)) {
          $aRowSQL[] = '?';
          $aRowArgs[] = $mValue ? 1 : 0;
          continue;
        }

        if (is_null($mValue)) {
          $aRowSQL[] = 'NULL';
          continue;
        }

        continue 2; // Skip this row due to malformed data.
      }

      $aRows[] = '(' . implode(', ', $aRowSQL) . ')';
      $aArgs = array_merge($aArgs, $aRowArgs);
    }

    $aHeaders = array_map(fn($cKey) => "`{$cKey}`", $aHeaders);
    $cHeaders = '(' . implode(', ', $aHeaders) . ')';
    $cValues  = implode(",\n", $aRows);

    return [
      $cHeaders . "\nVALUES\n" . $cValues,
      $aArgs,
    ];
  }

  /**
   * Flushes model deletes from the database.
   */
  public static function FlushModelDeletes(string $cModel): void
  {
    $cTable = $cModel::TABLE;
    $bSoftDeletes = $cModel::SOFT_DELETES;
    $cConnection = $cModel::CONNECTION;

    if (!$bSoftDeletes) return;

    if (!preg_match('/^[\da-z_]+$/i', $cTable)) {
      throw new \InvalidArgumentException('Invalid table name');
    }
    $oPDO = $cConnection::Query("DELETE FROM `$cTable` WHERE `__deleted_at` < DATE_SUB(NOW(), INTERVAL 1 HOUR);");
  }
}
