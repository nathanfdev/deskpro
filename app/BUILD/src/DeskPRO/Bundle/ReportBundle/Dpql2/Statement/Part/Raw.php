<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a raw SQL part.
 */
class Raw extends AbstractPart
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
        return new Prepared($this->sql, $this->sql);
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return $this->sql;
    }
}
