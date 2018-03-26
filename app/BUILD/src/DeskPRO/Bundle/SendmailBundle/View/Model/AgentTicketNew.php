<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class AgentTicketNew extends AgentTicketEmailType
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
     * @param string $tac
     */
    public function setTac($tac)
    {
        $this->tac = $tac;
    }

    protected $templateFile = 'emails_agent:ticket_new.html.twig';
}
