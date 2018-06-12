<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;

class TicketDateTermsHandler extends AbstractTermsHandler
{
    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            TermFieldIds::TICKET_DATE_CREATED,
            TermFieldIds::TICKET_DATE_RESOLVED,
            TermFieldIds::TICKET_DATE_LAST_AGENT_REPLY,
            TermFieldIds::TICKET_DATE_LAST_USER_REPLY,
            TermFieldIds::TICKET_DATE_AGENT_WAITING,
            TermFieldIds::TICKET_DATE_USER_WAITING,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::TICKET_DATE_CREATED:
                $fieldValue = $ticketModel->date_created;
                break;
            case TermFieldIds::TICKET_DATE_LAST_AGENT_REPLY:
                $fieldValue = $ticketModel->date_last_agent_reply;
                break;
            case TermFieldIds::TICKET_DATE_LAST_USER_REPLY:
                $fieldValue = $ticketModel->date_last_user_reply;
                break;
            case TermFieldIds::TICKET_DATE_AGENT_WAITING:
                $fieldValue = $ticketModel->date_agent_waiting;
                break;
            case TermFieldIds::TICKET_DATE_USER_WAITING:
                $fieldValue = $ticketModel->date_user_waiting;
                break;
            default:
                throw new \InvalidArgumentException();
        }

        return $this->checkValue($fieldValue, $operator, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        $column = null;
        switch ($fieldId) {
            case TermFieldIds::TICKET_DATE_CREATED:
                $column = '{tickets}.date_created';
                break;
            case TermFieldIds::TICKET_DATE_RESOLVED:
                $column = '{tickets}.date_resolved';
                break;
            case TermFieldIds::TICKET_DATE_LAST_USER_REPLY:
                $column = '{tickets}.date_last_user_reply';
                break;
            case TermFieldIds::TICKET_DATE_LAST_AGENT_REPLY:
                $column = '{tickets}.date_last_agent_reply';
                break;
            case TermFieldIds::TICKET_DATE_AGENT_WAITING:
                $column = '{tickets}.date_agent_waiting';
                break;
            case TermFieldIds::TICKET_DATE_USER_WAITING:
                $column = '{tickets}.date_user_waiting';
                break;
        }

        if ($column) {
            return $this->checkValueQueryCondition($column, $operator, $options);
        }

        throw new \InvalidArgumentException('Unknown field');
    }
}
