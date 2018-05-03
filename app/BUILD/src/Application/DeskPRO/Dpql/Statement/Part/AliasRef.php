<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;

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
     * @param string $alias
     */
    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Prepares a part for use, including validating that the usage is valid.
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
    public function prepare(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    ) {
        if (!in_array($section, ['split', 'group', 'order'])) {
            throw new Exception('Alias references may only be used in SPLIT BY, GROUP BY, and ORDER BY sections.');
        }

        $fieldId = $statement->getSqlSelectFieldId($this->alias);
        $sql     = ($fieldId !== false ? $select->getSelectField($fieldId) : 'NULL');

        return new Prepared($sql, $this->alias);
    }

    /**
     * Renders a part back to DPQL.
     *
     * @param \Application\DeskPRO\Dpql\Statement\Display             $statement
     * @param string                                                  $section
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack
     *
     * @return string
     */
    public function toDpql(Display $statement, $section, array $stack)
    {
        return '@'.$statement->quoteDpqlString($this->alias);
    }
}
