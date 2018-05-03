<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;

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
     * @param float|int $number
     */
    public function __construct($number)
    {
        $this->number = $number;
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
        if (!$stack && in_array($section, ['group', 'order'])) {
            throw new Exception('Numbers may not be referenced directly at the root of the GROUP BY or ORDER BY sections.');
        }

        $value = strval($this->number + 0);

        return new Prepared($value, $value, false, 'number');
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
