<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions\ActionDef;

use Application\DeskPRO\Entity\TicketActionDef;
use Orb\Util\Arrays;

class TicketActionDefManager
{
    /**
     * @var \Application\DeskPRO\Entity\TicketActionDef[]
     */
    private $ticket_action_defs;

    /**
     * @var \Application\DeskPRO\Entity\TicketActionDef[]
     */
    private $names_to_def;


    /**
     * @var \Application\DeskPRO\Entity\TicketActionDef[]
     */
    public function __construct(array $ticket_action_defs)
    {
        $this->ticket_action_defs = Arrays::keyFromData($ticket_action_defs, 'id');
        $this->names_to_def = Arrays::keyFromData($ticket_action_defs, 'action_name');
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketActionDef[]
     */
    public function getAllDefs()
    {
        return array_values($this->ticket_action_defs);
    }

    /**
     * @param  int  $id
     * @return bool
     */
    public function hasDef($id)
    {
        return isset($this->ticket_action_defs[$id]);
    }

    /**
     * @param  int                       $id
     * @return TicketActionDef
     * @throws \InvalidArgumentException
     */
    public function getDef($id)
    {
        if (!isset($this->ticket_action_defs[$id])) {
            throw new \InvalidArgumentException();
        }

        return $this->ticket_action_defs[$id];
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function hasNamedDef($name)
    {
        return isset($this->names_to_def[$name]);
    }

    /**
     * @param  string                    $name
     * @return TicketActionDef
     * @throws \InvalidArgumentException
     */
    public function getNamedDef($name)
    {
        if (!isset($this->names_to_def[$name])) {
            throw new \InvalidArgumentException();
        }

        return $this->names_to_def[$name];
    }
}
