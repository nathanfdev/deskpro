<?php

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\SendmailBundle\View\Model\CustomTemplate;

class CustomViewModelFactory extends AbstractViewModelFactory
{
    /**
     * @param Ticket $ticket
     *
     * @throws \Exception
     *
     * @return CustomTemplate
     */
    public function createCustomTemplateModel(Ticket $ticket)
    {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(CustomTemplate::class, $arguments);
    }
}
