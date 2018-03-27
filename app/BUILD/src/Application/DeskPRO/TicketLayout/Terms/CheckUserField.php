<?php

namespace Application\DeskPRO\TicketLayout\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Orb\Util\CheckedOptionsArray;

class CheckUserField extends \Application\DeskPRO\Tickets\Triggers\Terms\CheckUserField implements TicketLayoutTermInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTicketFieldValueJs($id)
    {
        return "ticket.getUserFieldValue($id)";
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('field_id', 'value', 'type_name');
        $options->setAliases('field_id', ['field']);

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTicketMatch(Ticket $ticket)
    {
        $context = new ExecutorContext();

        return $this->isTriggerMatch($ticket, $context);
    }

    /**
     * @return string
     */
    public function getTermType()
    {
        $class = get_class($this);
        $parts = explode('\\', $class);
        $class = end($parts);

        return $class.$this->getTermOptions()->get('field_id');
    }
}
