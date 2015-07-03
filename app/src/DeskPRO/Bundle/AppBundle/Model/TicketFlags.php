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

/**
 * Pseudo-implementation of ticket flags. Those were hard-coded so this makes coupling a
 * little more loose.
 */
class TicketFlags
{
    protected $flags = array(
        'blue',
        'green',
        'orange',
        'pink',
        'purple',
        'red',
        'yellow',
    );

    /**
     * Get the list of available flags.
     * @return array[string] the list of all ticket flag names
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Checks if a flag name is a valid ticket flag.
     * @param string $flag_name is the ticket flag's name to be tested.
     * @return bool
     */
    public function flagIsValid($flag_name)
    {
        return in_array($flag_name, $this->flags);
    }
}
