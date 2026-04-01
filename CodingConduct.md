# Coding conduct for Perritu/LeanDB

This is a short description of the coding conduct used by this project.

## Coding settings

This project is built on top of the PHP PSR-12 coding standard, with the following modifications:

- **Indentation**: 2 spaces
- **Line length**: 120 characters
- **Variable names**: Hungarian notation
- **Class and Method names**: PascalCase (like camelCase, but with the very first letter capitalized)
- **Constant names**: UPPERCASE

For convenience, this porject also includes a `.editorconfig` file to set these settings for editors that support it.

### Hungarian notation

The following prefixes are used for variable names:

| Prefix | Description            | Example               |
| ------ | ---------------------- | --------------------- |
| `$a`   | Arrays                 | `array $aList`        |
| `$b`   | Boolean                | `bool $bTerminated`   |
| `$c`   | Strings                | `string $cName`       |
| `$i`   | Integer for indexes    | `int $iId`            |
| `$n`   | Integer for quantity   | `int $nCount`         |
| `$u`   | Unsigned integer       | `int $uId`            |
| `$f`   | Float or double nums.  | `float $fCalc`        |
| `$r`   | Resources              | `$rFile = fopen(...)` |

The following prefixes are also added to standard CS or Cpp hungarian notation:

| Prefix | Description            | Example                  |
| ------ | ---------------------- | ------------------------ |
| `$bit` | Bit flags              | `int $bitFlags`          |
| `$err` | Error handlers         | `catch (Exception $err)` |
| `$o`   | Objects                | `object $oObject`        |

## Documentation

When writing or updating documentation, do so in english and follow the PHP standard for doc pages.

Add each entry on it's own file in the `docs` directory. The entry name should be the same as the class it documents.
