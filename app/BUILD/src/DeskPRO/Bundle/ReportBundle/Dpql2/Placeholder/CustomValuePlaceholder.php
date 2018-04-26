<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

class CustomValuePlaceholder extends AbstractPlaceholder
{
    /**
     * @var int|string|float
     */
    private $value;

    /**
     * CustomIntegerPlaceholder constructor.
     *
     * @param DpqlContextStorage $dpqlContextStorage
     * @param                    $value
     */
    public function __construct(DpqlContextStorage $dpqlContextStorage, $value)
    {
        parent::__construct($dpqlContextStorage);
        $this->value = $value;
    }

    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        return new Prepared($select->quoteForSql($this->value));
    }

    public function prepareWithIntervals(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result, array $intervals = [])
    {
        return new Prepared($select->quoteForSql($this->value));
    }

    public function prepareComparison(
        AbstractPart $lhs, $comparison, SelectPart $statement, $section, array $stack,
        SqlSelect $select, ResultMetadata $result, array $intervals = []
    ) {
        $lhsRes = $lhs->prepare($statement, $section, $stack, $select, $result);

        $lhsSql     = $lhsRes->sql();
        $lhsName    = $lhsRes->name();
        $dpql       = $this->_toDpql();
        $outputName = "$lhsName $comparison $dpql";

        $sqlValue = $select->quoteForSql($this->value);

        switch ($comparison) {
            case '=':
                $sql = "$lhsSql = $sqlValue";
                break;

            case '<>':
                $sql = "$lhsSql != $sqlValue";
                break;

            case '>':
                $sql = "$lhsSql > $sqlValue";
                break;

            case '>=':
                $sql = "$lhsSql >= $sqlValue";
                break;

            case '<':
                $sql = "$lhsSql < $sqlValue";
                break;

            case '<=':
                $sql = "$lhsSql <= $sqlValue";
                break;

            default:
                $sql = '0';
                break;
        }

        return new Prepared("($sql)", $outputName);
    }
}
