<?php

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\SendmailBundle\View\Model\CustomTemplate;

class CustomViewModelFactory extends AbstractViewModelFactory
{
    /**
     * @return CustomTemplate
     */
    public function createCustomTemplateModel(Ticket $ticket)
    {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(CustomTemplate::class, $arguments);
    }
}
