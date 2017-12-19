<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql2\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql2\Exception as DpqlException;
use Application\DeskPRO\Dpql2\Statement\SelectPart;

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
     * @var \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[]
     */
    protected $_arguments;

    /**
     * Prepares the function for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql2\Statement\SelectPart          $statement
     * @param string                                                   $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart[] $stack     Parent parts
     * @param \Application\DeskPRO\Dpql2\SqlSelect                     $select    Select being built up
     * @param \Application\DeskPRO\Dpql2\ResultHandler                 $result
     *
     * @throws \Application\DeskPRO\Dpql2\Exception
     *
     * @return \Application\DeskPRO\Dpql2\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    abstract public function prepare(
        SelectPart $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
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
     * @return \Application\DeskPRO\Dpql2\Func\AbstractFunc
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
     * @param \Application\DeskPRO\Dpql2\Statement\Part\AbstractPart $part
     *
     * @throws \Application\DeskPRO\Dpql2\Exception
     *
     * @return mixed
     */
    protected function _toLiteral(\Application\DeskPRO\Dpql2\Statement\Part\AbstractPart $part)
    {
        if ($part instanceof \Application\DeskPRO\Dpql2\Statement\Part\StringPart) {
            return $part->string;
        } elseif ($part instanceof \Application\DeskPRO\Dpql2\Statement\Part\Number) {
            return $part->number;
        } else {
            throw new DpqlException('Only literal values may be used for '.$this->_name.'() parameters.');
        }
    }
}
