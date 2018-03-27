<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

/**
 * Abstract base for all placeholder (%NAME%) references.
 */
abstract class AbstractPlaceholder
{
    /**
     * Maps placeholders from the DPQL reference (in upper case) to the name
     * of the class in the \Application\DeskPRO\Dpql\Placeholder namespace.
     *
     * @var array
     */
    protected static $_placeholderMap = [
        'EVER'           => 'Ever',
        'LAST_MONTH'     => 'LastMonth',
        'LAST_WEEK'      => 'LastWeek',
        'LAST_YEAR'      => 'LastYear',
        'PAST_24_HOURS'  => 'Past24Hours',
        'PAST_12_HOURS'  => 'Past12Hours',
        'PAST_HOUR'      => 'PastHour',
        'PAST_7_DAYS'    => 'Past7Days',
        'PAST_30_DAYS'   => 'Past30Days',
        'PAST_6_MONTHS'  => 'Past6Months',
        'PAST_12_MONTHS' => 'Past12Months',
        'THIS_MONTH'     => 'ThisMonth',
        'THIS_WEEK'      => 'ThisWeek',
        'THIS_YEAR'      => 'ThisYear',
        'TODAY'          => 'Today',
        'TOMORROW'       => 'Tomorrow',
        'YESTERDAY'      => 'Yesterday',
    ];

    /**
     * Name of the placeholder, in user-specified case.
     *
     * @var string
     */
    protected $_name;

    /**
     * Prepares the placeholder for use, including validating that the usage is valid.
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
     * Prepares the placeholder for use, including validating that the usage is valid.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Display               $statement
     * @param string                                                    $section   Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]   $stack     Parent parts
     * @param \Application\DeskPRO\Dpql\SqlSelect                       $select    Select being built up
     * @param \Application\DeskPRO\Dpql\ResultHandler                   $result
     * @param \Application\DeskPRO\Dpql\Statement\Part\BinaryInterval[] $intervals List of intervals that affect this calculation
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepareWithIntervals(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result, array $intervals = []
    ) {
        return $this->prepare($statement, $section, $stack, $select, $result);
    }

    /**
     * Create through the create() factory method.
     *
     * @param string $name
     */
    protected function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * Prepares the placeholder when it's called in a binary comparison context.
     * The placeholder is always the right hand side of the comparison. This is
     * needed for placeholders that actually map to date ranges rather than scalars.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart     $lhs        The left hand side of the comparison
     * @param string                                                    $comparison The comparison operator
     * @param \Application\DeskPRO\Dpql\Statement\Display               $statement
     * @param string                                                    $section    Name of the section usage is in (select, where, split, group, order)
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[]   $stack      Parent parts
     * @param \Application\DeskPRO\Dpql\SqlSelect                       $select
     * @param \Application\DeskPRO\Dpql\ResultHandler                   $result
     * @param \Application\DeskPRO\Dpql\Statement\Part\BinaryInterval[] $intervals  List of intervals that affect this calculation
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false the default behavior should be called
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, Display $statement, $section, array $stack,
        Dpql\SqlSelect $select, Dpql\ResultHandler $result, array $intervals = []
    ) {
        return false;
    }

    /**
     * Gets the placeholder in DPQL.
     *
     * @return string
     */
    protected function _toDpql()
    {
        return '%'.strtoupper($this->_name).'%';
    }

    /**
     * Creates the placeholder with the specific name.
     *
     * @param string $name
     *
     * @throws \Application\DeskPRO\Dpql\Exception
     *
     * @return \Application\DeskPRO\Dpql\Placeholder\AbstractPlaceholder
     */
    public static function create($name)
    {
        $name = strtoupper($name);
        if (isset(self::$_placeholderMap[$name])) {
            $map = __NAMESPACE__.'\\'.self::$_placeholderMap[$name];

            return new $map($name);
        } else {
            throw new Exception("Unknown placeholder $name specified.");
        }
    }
}
