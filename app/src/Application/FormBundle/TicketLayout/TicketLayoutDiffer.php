<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;

/**
 * A utility that TicketType uses to find the differences between various layouts so that it can consturct itself
 * and then reconstruct itself based on incoming data.
 */
class TicketLayoutDiffer
{
    /**
     * Given we have established an $initial_layout, and we want to change to $destination_layout, what are
     * the fields that I should remove? (note: doesnt address adding new fields, see findFieldsToAdd())
     * ie. what were the excess Fields in the initial layout?
     *
     * @param Layout $initial_layout
     * @param Layout $destination_layout
     * @return LayoutField[]
     */
    public function findFieldsToRemove(Layout $initial_layout, Layout $destination_layout)
    {
        $fields_to_remove = array();

        foreach ($initial_layout->all() as $field) {
            if (!$destination_layout->has($field->getId())) {
                $fields_to_remove[] = $field;
            }
        }

        return $fields_to_remove;
    }

    /**
     * Given the $initial_layout, what would we need to add to it to get it to be the $destination_layout?
     * ie. What Fields are missing in our initial layout?
     * 
     * @param Layout $initial_layout
     * @param Layout $destination_layout
     * @return LayoutField[]
     */
    public function findFieldsToAdd(Layout $initial_layout, Layout $destination_layout)
    {
        $fields_to_add = array();

        foreach ($destination_layout->all() as $field) {
            if (!$initial_layout->has($field->getId())) {
                $fields_to_add[] = $field;
            }
        }

        return $fields_to_add;
    }
}
