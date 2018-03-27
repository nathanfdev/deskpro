<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class Union.
 */
class Union extends AbstractPart
{
    /**
     * @var array
     */
    private $parts;

    /**
     * Constructor.
     *
     * @param array $parts
     */
    public function __construct(array $parts)
    {
        $this->parts = $parts;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $sqlParts = [];
        $first    = true;

        foreach ($this->parts as $partInfo) {
            /** @var SelectPart $part */
            list($type, $part) = $partInfo;
            $part->prepare();

            if ($first) {
                $sqlParts[] = '('.$part->toSql().')';
            } else {
                $op         = 'UNION '.($type === 'ANY' ? 'ALL' : 'DISTINCT');
                $sqlParts[] = "\n$op\n".'('.$part->toSql().')';
            }

            $first = false;
        }

        $sql = implode('', $sqlParts);

        return new Prepared("\n$sql\n", 'UNION subqueries', false, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        $dpqlParts = [];
        $first     = true;

        foreach ($this->parts as $partInfo) {
            /** @var SelectPart $part */
            list($type, $part) = $partInfo;

            if ($first) {
                $dpqlParts[] = '('.$part->toDpql().')';
            } else {
                $op          = 'UNION '.($type === 'ANY' ? 'ALL' : 'DISTINCT');
                $dpqlParts[] = "\n$op\n".'('.$part->toDpql().')';
            }

            $first = false;
        }

        return implode('', $dpqlParts);
    }
}
