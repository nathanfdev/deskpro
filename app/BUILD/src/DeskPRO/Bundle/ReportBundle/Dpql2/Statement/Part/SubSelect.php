<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class SubSelect.
 */
class SubSelect extends AbstractPart
{
    /**
     * @var string
     */
    private $sql = '';

    /**
     * Constructor.
     *
     * @param string $sql
     */
    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $sql = $this->sql;
        if ($sql instanceof SelectPart) {
            $sql = $sql->toSql();
        }

        return new Prepared("($sql)", $sql);
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        if ($this->sql instanceof SelectPart) {
            return $this->sql->toDpql();
        }

        return $this->sql;
    }
}
