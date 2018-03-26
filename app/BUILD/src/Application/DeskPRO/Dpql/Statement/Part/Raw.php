<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Statement\Part;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Display;

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
     * @param string $sql
     */
    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    ) {
        return new Prepared($this->sql, $this->sql);
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(Display $statement, $section, array $stack)
    {
        return $this->sql;
    }
}
