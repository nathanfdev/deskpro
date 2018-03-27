<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a number in DPQL.
 */
class Number extends AbstractPart
{
    /**
     * @var float|int
     */
    public $number;

    /**
     * Constructor.
     *
     * @param float|int $number
     */
    public function __construct($number)
    {
        $this->number = $number;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        if (!$stack && in_array($section, ['group', 'order'])) {
            throw new DpqlException('Numbers may not be referenced directly at the root of the GROUP BY or ORDER BY sections.');
        }

        $value = strval($this->number + 0);

        return new Prepared($value, $value, false, 'number');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return strval($this->number + 0);
    }

    /**
     * @return float|int
     */
    public function getValue()
    {
        return $this->number;
    }
}
