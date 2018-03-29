<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;

/**
 * Represents an alias (X AS Y) part of a DPQL statement.
 */
class Alias extends AbstractPart
{
    /**
     * @var \Application\DeskPRO\Dpql\Statement\Part\AbstractPart
     */
    public $value;

    /**
     * @var string
     */
    public $alias;

    /**
     * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart $value
     * @param string                                                $alias
     */
    public function __construct(AbstractPart $value, $alias)
    {
        $this->value = $value;
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
        throw new Exception('Alias prepare() cannot not be called');
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
        return $this->value->toDpql($statement, $section, $stack)
            .' AS '.$statement->quoteDpqlString($this->alias);
    }
}
