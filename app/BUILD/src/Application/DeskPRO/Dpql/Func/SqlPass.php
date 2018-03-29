<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;

/**
 * This is used for functions that are simply passed through to MySQL's internal behavior.
 */
class SqlPass extends AbstractFunc
{
    /**
     * Lists valid MySQL functions (in keys, upper case) to the data type
     * returned and the number of arguments they can take. Value is an array
     * with 2 or 3 elements:
     *  - 0: data type returned
     *  - 1: minimum (or only) number or arguments allowed
     *  - 2: (optional) maximum number of arguments allowed; -1 for unlimited; omit for same as minimum.
     *
     * @var array
     */
    protected static $_functions = [
        'ABS'               => ['number', 1],
        'ACOS'              => ['number', 1],
        'ADDDATE'           => ['date', 2],
        'ADDTIME'           => ['datetime', 2],
        'ASCII'             => ['number', 1],
        'ASIN'              => ['number', 1],
        'ATAN'              => ['number', 1],
        'ATAN2'             => ['number', 2],
        'AVG'               => ['number', 1],
        'BIN'               => ['string', 1],
        'BIT_AND'           => ['number', 1],
        'BIT_COUNT'         => ['number', 1],
        'BIT_LENGTH'        => ['number', 1],
        'BIT_OR'            => ['number', 1],
        'BIT_XOR'           => ['number', 1],
        'CEIL'              => ['number', 1],
        'CEILING'           => ['number', 1],
        'CHAR'              => ['string', 1, -1],
        'CHAR_LENGTH'       => ['number', 1],
        'CHARACTER_LENGTH'  => ['number', 1],
        'COALESCE'          => ['string', 1, -1],
        'CONCAT'            => ['string', 2, -1],
        'CONCAT_WS'         => ['string', 3, -1],
        'CONV'              => ['string', 3],
        'COS'               => ['number', 1],
        'COT'               => ['number', 1],
        'CRC32'             => ['number', 1],
        'DATE_FORMAT'       => ['string', 2],
        'DATEDIFF'          => ['number', 2],
        'DAYOFYEAR'         => ['number', 1],
        'DAY'               => ['number', 1],
        'DEGREES'           => ['number', 1],
        'ELT'               => ['string', 4, -1],
        'EXP'               => ['number', 1],
        'EXPORT_SET'        => ['string', 3, 5],
        'FIELD'             => ['number', 2, -1],
        'FIND_IN_SET'       => ['number', 2],
        'FLOOR'             => ['number', 1],
        'FROM_DAYS'         => ['date', 1],
        'FROM_UNIXTIME'     => ['datetime', 1],
        'GREATEST'          => ['number', 2, -1],
        'GROUP_CONCAT'      => ['string', 1],
        'HEX'               => ['string', 1],
        'IF'                => ['mixed', 3],
        'IFNULL'            => ['mixed', 2],
        'INET_ATON'         => ['string', 1], // returning string as may be a big number and shouldn't be formatted anyway
        'INET_NTOA'         => ['string', 1],
        'INSERT'            => ['string', 4],
        'INSTR'             => ['string', 2],
        'INTERVAL'          => ['number', 4, -1],
        'ISNULL'            => ['boolean', 1],
        'LAST_DAY'          => ['number', 1],
        'LCASE'             => ['string', 1],
        'LEAST'             => ['number', 2, -1],
        'LEFT'              => ['string', 2],
        'LENGTH'            => ['number', 1],
        'LN'                => ['number', 1],
        'LOCATE'            => ['number', 2, 3],
        'LOG10'             => ['number', 1],
        'LOG2'              => ['number', 1],
        'LOG'               => ['number', 1, 2],
        'LOWER'             => ['string', 1],
        'LPAD'              => ['string', 3],
        'LTRIM'             => ['string', 1],
        'MAKE_SET'          => ['string', 3],
        'MAKEDATE'          => ['date', 2],
        'MAKETIME'          => ['time', 3],
        'MAX'               => ['number', 1],
        'MICROSECOND'       => ['number', 1],
        'MID'               => ['string', 3],
        'MIN'               => ['number', 1],
        'MOD'               => ['number', 2],
        'NULLIF'            => ['string', 2],
        'OCT'               => ['string', 1],
        'OCTET_LENGTH'      => ['number', 1],
        'ORD'               => ['number', 1],
        'PERIOD_ADD'        => ['numberraw', 2],
        'PERIOD_DIFF'       => ['numberraw', 2],
        'POW'               => ['number', 2],
        'POWER'             => ['number', 2],
        'QUARTER'           => ['number', 1],
        'RADIANS'           => ['number', 1],
        'RAND'              => ['number', 0, 1],
        'REPEAT'            => ['string', 2],
        'REPLACE'           => ['string', 3],
        'REVERSE'           => ['string', 1],
        'RIGHT'             => ['string', 2],
        'ROUND'             => ['number', 1, 2],
        'RPAD'              => ['string', 3],
        'RTRIM'             => ['string', 1],
        'SEC_TO_TIME'       => ['time', 1],
        'SECOND'            => ['number', 1],
        'SIGN'              => ['number', 1],
        'SIN'               => ['number', 1],
        'SOUNDEX'           => ['string', 1],
        'SPACE'             => ['string', 1],
        'SQRT'              => ['number', 1],
        'STDDEV_POP'        => ['number', 1],
        'STDDEV_SAMP'       => ['number', 1],
        'STR_TO_DATE'       => ['date', 2],
        'STRCMP'            => ['number', 2],
        'SUBDATE'           => ['date', 2],
        'SUBSTR'            => ['string', 2, 3],
        'SUBSTRING'         => ['string', 2, 3],
        'SUBSTRING_INDEX'   => ['string', 3],
        'SUBTIME'           => ['datetime', 2],
        'SUM'               => ['number', 1],
        'TAN'               => ['number', 1],
        'TIME'              => ['time', 1],
        'TIME_FORMAT'       => ['string', 2],
        'TIME_TO_SEC'       => ['number', 1],
        'TIMEDIFF'          => ['time', 2],
        'TIMESTAMP'         => ['datetime', 1, 2],
        'TO_DAYS'           => ['number', 1],
        'TO_SECONDS'        => ['number', 1],
        'TRIM'              => ['string', 1],
        'TRUNCATE'          => ['number', 1],
        'UCASE'             => ['string', 1],
        'UNHEX'             => ['string', 1],
        'UNIX_TIMESTAMP'    => ['number', 1],
        'UPPER'             => ['string', 1],
        'UTC_DATE'          => ['date', 0],
        'UTC_TIME'          => ['time', 0],
        'UTC_TIMESTAMP'     => ['datetime', 0],
        'VAR_POP'           => ['number', 1],
        'VAR_SAMP'          => ['number', 1],
        'WEEK'              => ['number', 1, 2],
        'WEEKDAY'           => ['number', 1],
        'WEEKOFYEAR'        => ['number', 1],
        'YEARWEEK'          => ['numberraw', 1, 2],
        'CURRENT_DATE'      => ['date', 0],
        'CURRENT_TIME'      => ['time', 0],
        'CURRENT_TIMESTAMP' => ['datetime', 0],
        'CONVERT_TZ'        => ['datetime', 3],
    ];

    /**
     * Prepares the function for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Display             $statement
     * @param string                                                  $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack     Parent parts
     * @param \Application\DeskPRO\Dpql\SqlSelect                     $select    Select being built up
     * @param \Application\DeskPRO\Dpql\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepare(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    ) {
        $name       = $this->_name;
        $lookupName = strtoupper($name);

        if (!isset(self::$_functions[$lookupName])) {
            throw new Exception("Invalid DPQL function $name.");
        }

        $info      = self::$_functions[$lookupName];
        $givenArgs = count($this->_arguments);

        if (isset($info[2])) {
            $minArgs = $info[1];
            $maxArgs = $info[2];

            if ($givenArgs < $minArgs) {
                throw new Exception("DPQL function $name expects at least $minArgs argument(s).");
            }
            if ($maxArgs >= 0 && $givenArgs > $maxArgs) {
                throw new Exception("DPQL function $name expects at least $maxArgs argument(s).");
            }
        } elseif ($givenArgs != $info[1]) {
            throw new Exception("DPQL function $name expects $info[1] argument(s).");
        }

        $valuesSql   = [];
        $valuesNames = [];
        foreach ($this->_arguments as $arg) {
            $prepped       = $arg->prepare($statement, $section, $stack, $select, $result);
            $valuesSql[]   = $prepped->sql();
            $valuesNames[] = $prepped->name();
        }

        $sql = strtoupper($this->_name).'('.implode(', ', $valuesSql).')';

        return new Prepared($sql, "$this->_name(".implode(', ', $valuesNames).')', false, $info[0]);
    }
}
