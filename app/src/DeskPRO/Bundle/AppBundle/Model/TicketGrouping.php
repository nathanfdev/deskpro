<?php

/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Model;

use DeskPRO\Bundle\AppBundle\Exception\UnknownTicketGroupingColumnException;
use \ReflectionClass;

/**
 * Defines grouping columns for the Ticket table.
 */
class TicketGrouping
{
    const DEPARTMENT      = 'department';
    const PERSON          = 'person';
    const AGENT           = 'agent';
    const DATE_CREATED    = 'date_created';

    // Contains this grouping's value.
    protected $column = null;

    /**
     * This class must either be instanciated with ::fromString() or ::fromConst().
     */
    protected function __construct($column)
    {
        $this->column = $column;
    }

    public static function fromString($col_string)
    {
        // A tiny bit of magic. Need PHP 5.3+
        $constant = constant(sprintf('%s::%s', __CLASS__, strtoupper($col_string)));

        if (null === $constant) {
            throw new UnknownTicketGroupingColumnException(
                sprintf("Column %s can't be used to group tickets.", $col_string)
            );
        }

        return new self($constant);
    }

    public static function fromConst($value)
    {
        $refl = new \ReflectionClass(__CLASS__);
        $constants = $refl->getConstants();

        if (false === array_search($value, $constants)) {
            throw new UnknownTicketGroupingColumnException();
        }

        return new self($value);
    }

    function getColumn()
    {
        return $this->column;
    }
}
