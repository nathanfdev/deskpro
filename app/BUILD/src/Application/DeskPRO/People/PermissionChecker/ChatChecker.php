<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionChecker;

use Application\DeskPRO\Entity\ChatConversation;

class ChatChecker extends AbstractChecker
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @param \Application\DeskPRO\Entity\ChatConversation $convo
     *
     * @return bool
     */
    public function canView(ChatConversation $convo)
    {
        if ($convo->status == ChatConversation::STATUS_ENDED && !$this->person->hasPerm('agent_chat.view_transcripts')) {
            return false;
        }

        // Cant be an agent chat, obviously
        if ($convo->is_agent) {
            return false;
        }

        //------------------------------
        // If the user is part of the chat
        // then we know right away they can view
        //------------------------------

        if ($convo->agent && $convo->agent->id == $this->person->id) {
            return true;
        }

        if (in_array($this->person->id, $convo->getParticipantIds())) {
            return true;
        }

        //------------------------------
        // Can't view certain deps
        //------------------------------

        if ($convo->department && !$this->person->getHelper('AgentPermissions')->isDepartmentAllowed($convo->department, 'chat')) {
            return false;
        }

        //------------------------------
        // Cant view unassigned
        //------------------------------

        if (!$convo->agent && !$this->person->hasPerm('agent_chat.view_unassigned')) {
            return false;
        }

        //------------------------------
        // Cant view others
        //------------------------------

        if ($convo->agent && $convo->agent !== $this->person && !$this->person->hasPerm('agent_chat.view_others')) {
            return false;
        }

        // If we got here, then we're allowed
        return true;
    }

    /**
     * @param \Application\DeskPRO\Entity\ChatConversation $convo
     *
     * @return bool
     */
    public function canDelete(ChatConversation $convo)
    {
        if (!$this->canView($convo) || !$this->person->hasPerm('agent_chat.delete')) {
            return false;
        }

        return false;
    }
}
