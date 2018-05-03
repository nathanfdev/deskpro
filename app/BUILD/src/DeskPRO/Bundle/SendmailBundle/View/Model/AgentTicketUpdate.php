<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class AgentTicketUpdate extends AgentTicketEmailType
{
    /**
     * The ticket access code.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $tac;

    /**
     * A flag describing the action that occurred (assigned, assigned_team, added_part or status_changed).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $typeFlag;

    /**
     * @param string $tac
     */
    public function setTac($tac)
    {
        $this->tac = $tac;
    }

    /**
     * @param string $typeFlag
     */
    public function setTypeFlag($typeFlag)
    {
        $this->typeFlag = $typeFlag;
    }

    protected $templateFile = 'emails_agent:ticket_update.html.twig';
}
