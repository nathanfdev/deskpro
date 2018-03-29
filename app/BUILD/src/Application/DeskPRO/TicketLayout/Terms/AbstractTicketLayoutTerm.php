<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Exception\NotImplementedException;
use Orb\Util\Util;

abstract class AbstractTicketLayoutTerm implements TicketLayoutTermInterface
{
    const OP_IS  = 'is';
    const OP_NOT = 'not';

    /**
     * @var
     */
    protected $op;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param string $op
     * @param array  $options
     */
    public function __construct($op, array $options)
    {
        $this->op      = $op;
        $this->options = $options;
    }

    /**
     * Gets the type name of the criteria.
     *
     * @return string
     */
    public function getTermType()
    {
        return Util::getBaseClassname($this);
    }

    /**
     * Gets criteria operator (is, is not, etc).
     *
     * @return string
     */
    public function getTermOperator()
    {
        return $this->op;
    }

    /**
     * Get's an array of options.
     *
     * @return array
     */
    public function getTermOptions()
    {
        return $this->options;
    }

    /**
     * Used on the server-side to check if the term matches.
     *
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function isTicketMatch(Ticket $ticket)
    {
        // Cant be abstract in older versions of php see https://bugs.php.net/bug.php?id=43200
        throw new NotImplementedException();
    }

    /**
     * Should return a JS function that accepts a ticket object and returns true/false
     * depending on if the term passes/fails.
     *
     * @return string
     */
    public function compileJsCheck()
    {
        // Cant be abstract in older versions of php see https://bugs.php.net/bug.php?id=43200
        throw new NotImplementedException();
    }
}
