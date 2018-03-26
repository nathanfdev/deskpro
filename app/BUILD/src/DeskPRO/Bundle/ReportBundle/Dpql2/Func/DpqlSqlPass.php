<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * This is used for functions that are simply passed through to MySQL's internal behavior.
 */
class DpqlSqlPass extends AbstractDpqlFunc
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
        'FORMAT'            => ['number', 2, 3],
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
        'NOW'               => ['number', 0],
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
        'UNIX_TIMESTAMP'    => ['number', 0, 1],
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
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $name       = $this->name;
        $lookupName = strtoupper($name);

        if (!isset(self::$_functions[$lookupName])) {
            throw new DpqlException("Invalid DPQL function $name.");
        }

        $info      = self::$_functions[$lookupName];
        $givenArgs = count($arguments);

        if (isset($info[2])) {
            $minArgs = $info[1];
            $maxArgs = $info[2];

            if ($givenArgs < $minArgs) {
                throw new DpqlException("DPQL function $name expects at least $minArgs argument(s).");
            }
            if ($maxArgs >= 0 && $givenArgs > $maxArgs) {
                throw new DpqlException("DPQL function $name expects at least $maxArgs argument(s).");
            }
        } elseif ($givenArgs != $info[1]) {
            throw new DpqlException("DPQL function $name expects $info[1] argument(s).");
        }

        $valuesSql   = [];
        $valuesNames = [];
        foreach ($arguments as $arg) {
            $prepped       = $arg->prepare($statement, $section, $stack, $select, $metadata);
            $valuesSql[]   = $prepped->sql();
            $valuesNames[] = $prepped->name();
        }

        $sql = strtoupper($this->name).'('.implode(', ', $valuesSql).')';

        return new Prepared($sql, "$this->name(".implode(', ', $valuesNames).')', false, $info[0]);
    }
}
