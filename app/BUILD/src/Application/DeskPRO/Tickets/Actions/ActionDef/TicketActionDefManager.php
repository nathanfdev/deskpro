<?php

/**
 * DeskPRO.
 *
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
        $this->names_to_def       = Arrays::keyFromData($ticket_action_defs, 'action_name');
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketActionDef[]
     */
    public function getAllDefs()
    {
        return array_values($this->ticket_action_defs);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasDef($id)
    {
        return isset($this->ticket_action_defs[$id]);
    }

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     *
     * @return TicketActionDef
     */
    public function getDef($id)
    {
        if (!isset($this->ticket_action_defs[$id])) {
            throw new \InvalidArgumentException();
        }

        return $this->ticket_action_defs[$id];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasNamedDef($name)
    {
        return isset($this->names_to_def[$name]);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return TicketActionDef
     */
    public function getNamedDef($name)
    {
        if (!isset($this->names_to_def[$name])) {
            throw new \InvalidArgumentException();
        }

        return $this->names_to_def[$name];
    }
}
