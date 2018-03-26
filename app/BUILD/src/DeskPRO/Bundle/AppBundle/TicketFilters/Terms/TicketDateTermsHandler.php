<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;

class TicketDateTermsHandler extends AbstractTermsHandler
{
    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            Terms::TICKET_DATE_CREATED,
            Terms::TICKET_DATE_LAST_AGENT_REPLY,
            Terms::TICKET_DATE_LAST_USER_REPLY,
            Terms::TICKET_DATE_AGENT_WAITING,
            Terms::TICKET_DATE_USER_WAITING,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case Terms::TICKET_DATE_CREATED:          $fieldValue = $ticketModel->date_created; break;
            case Terms::TICKET_DATE_LAST_AGENT_REPLY: $fieldValue = $ticketModel->date_last_agent_reply; break;
            case Terms::TICKET_DATE_LAST_USER_REPLY:  $fieldValue = $ticketModel->date_last_user_reply; break;
            case Terms::TICKET_DATE_AGENT_WAITING:    $fieldValue = $ticketModel->date_agent_waiting; break;
            case Terms::TICKET_DATE_USER_WAITING:     $fieldValue = $ticketModel->date_user_waiting; break;
            default:
                throw new \InvalidArgumentException();
        }

        return $this->checkValue($fieldValue, $operator, $options);
    }
}
