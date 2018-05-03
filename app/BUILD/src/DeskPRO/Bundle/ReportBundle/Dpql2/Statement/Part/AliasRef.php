<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a reference to an alias (@'Alias') in a DPQL statement.
 */
class AliasRef extends AbstractPart
{
    /**
     * @var string
     */
    public $alias;

    /**
     * Constructor.
     *
     * @param string $alias
     */
    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        if (!in_array($section, ['split', 'group', 'order'])) {
            throw new DpqlException('Alias references may only be used in SPLIT BY, GROUP BY, and ORDER BY sections.');
        }

        $fieldId = $statement->getSqlSelectFieldId($this->alias);
        $sql     = ($fieldId !== false ? $select->getSelectField($fieldId) : 'NULL');

        return new Prepared($sql, $this->alias);
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return '@'.$statement->quoteDpqlString($this->alias);
    }
}
