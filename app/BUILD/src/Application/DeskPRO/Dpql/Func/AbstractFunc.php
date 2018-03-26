<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception as DpqlException;
use Application\DeskPRO\Dpql\Statement\Display;

/**
 * Abstract base for all DPQL function calls.
 */
abstract class AbstractFunc
{
    /**
     * Maps DPQL function names (in all upper case) to class names
     * (in the \Application\DeskPRO\Dqpl\Func namespace).
     *
     * @var array
     */
    protected static $_functionMap = [
        'ALIAS'                   => 'Alias',
        'COUNT'                   => 'Count',
        'COUNT_DISTINCT'          => 'CountDistinct',
        'CURDATE'                 => 'CurDate',
        'CURTIME'                 => 'CurTime',
        'DATE_OFFSET_GROUP'       => 'DateOffsetGroup',
        'DATE'                    => 'Date',
        'DAYNAME'                 => 'DayName',
        'DAYOFMONTH'              => 'DayOfMonth',
        'DAYOFWEEK'               => 'DayOfWeek',
        'FORMAT'                  => 'Format',
        'HIERARCHY'               => 'Hierarchy',
        'HIERARCHY_DESCENDS_FROM' => 'HierarchyDescendsFrom',
        'HOUR'                    => 'Hour',
        'LINK'                    => 'Link',
        'MATRIX'                  => 'Matrix',
        'MINUTE'                  => 'Minute',
        'MONTH'                   => 'Month',
        'MONTHNAME'               => 'MonthName',
        'NOW'                     => 'Now',
        'OBJ_LANG'                => 'ObjLang',
        'PERCENT'                 => 'Percent',
        'PRINT'                   => 'Printable',
        'TIME_LENGTH'             => 'TimeLength',
        'STACK_GROUP'             => 'StackGroup',
        'TO_UTC'                  => 'ToUtc',
        'TOTAL'                   => 'Total',
        'UTC'                     => 'Utc',
        'X'                       => 'X',
        'Y'                       => 'Y',
        'YEAR'                    => 'Year',
    ];

    /**
     * Name of the function (in user-provided case).
     *
     * @var string
     */
    protected $_name;

    /**
     * List of arguments for function.
     *
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]
     */
    protected $_arguments;

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
    abstract public function prepare(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    );

    /**
     * Constructor. Use the create() factory method.
     *
     * @param string $name
     * @param array  $arguments
     */
    protected function __construct($name, array $arguments = [])
    {
        $this->_name      = $name;
        $this->_arguments = $arguments;
    }

    /**
     * Creates the correct function handler object.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return \Application\DeskPRO\Dpql\Func\AbstractFunc
     */
    public static function create($name, array $arguments = [])
    {
        $name = strtoupper($name);
        if (isset(self::$_functionMap[$name])) {
            $map = __NAMESPACE__.'\\'.self::$_functionMap[$name];

            return new $map($name, $arguments);
        } else {
            return new SqlPass($name, $arguments);
        }
    }

    /**
     * Gets a literal value for the specified part.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $part
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return mixed
     */
    protected function _toLiteral(\Application\DeskPRO\Dpql\Statement\Part\AbstractPart $part)
    {
        if ($part instanceof \Application\DeskPRO\Dpql\Statement\Part\StringPart) {
            return $part->string;
        } elseif ($part instanceof \Application\DeskPRO\Dpql\Statement\Part\Number) {
            return $part->number;
        } else {
            throw new DpqlException('Only literal values may be used for '.$this->_name.'() parameters.');
        }
    }
}
