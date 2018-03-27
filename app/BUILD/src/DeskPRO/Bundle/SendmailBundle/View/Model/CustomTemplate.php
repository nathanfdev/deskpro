<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class CustomTemplate extends TicketEmailType
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

    protected $templateFile = 'emails_user:ticket_new_autoreply.html.twig';
}
