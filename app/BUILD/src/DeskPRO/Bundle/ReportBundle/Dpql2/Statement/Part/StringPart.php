<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a literal value in DPQL.
 */
class StringPart extends AbstractPart
{
    /**
     * @var string
     */
    public $string;

    /**
     * Constructor.
     *
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(
        SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result
    ) {
        return new Prepared($select->quoteForSql($this->string), $this->string, false, 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return $statement->quoteDpqlString($this->string);
    }

    public function getValue()
    {
        return $this->string;
    }
}
