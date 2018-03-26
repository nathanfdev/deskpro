<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;

class TicketBasicTermsHandler extends AbstractTermsHandler
{
    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            Terms::TICKET_ID,
            Terms::TICKET_STATUS,
            Terms::TICKET_DEPARTMENT,
            Terms::TICKET_AGENT,
            Terms::TICKET_AGENT_TEAM,
            Terms::TICKET_FOLLOWERS,
            Terms::TICKET_LANGUAGE,
            Terms::TICKET_PRODUCT,
            Terms::TICKET_CATEGORY,
            Terms::TICKET_PRIORITY,
            Terms::TICKET_URGENCY,
            Terms::TICKET_WORKFLOW,
            Terms::TICKET_LABELS,
            Terms::TICKET_EMAIL_ACCOUNT,
            Terms::TICKET_IS_HOLD,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case Terms::TICKET_ID:            $fieldValue = $ticketModel->id; break;
            case Terms::TICKET_STATUS:        $fieldValue = $ticketModel->status; break;
            case Terms::TICKET_DEPARTMENT:    $fieldValue = $ticketModel->department; break;
            case Terms::TICKET_AGENT:         $fieldValue = $ticketModel->agent; break;
            case Terms::TICKET_AGENT_TEAM:    $fieldValue = $ticketModel->agent_team; break;
            case Terms::TICKET_FOLLOWERS:     $fieldValue = $ticketModel->followers; break;
            case Terms::TICKET_LANGUAGE:      $fieldValue = $ticketModel->language; break;
            case Terms::TICKET_PRODUCT:       $fieldValue = $ticketModel->product; break;
            case Terms::TICKET_CATEGORY:      $fieldValue = $ticketModel->category; break;
            case Terms::TICKET_PRIORITY:      $fieldValue = $ticketModel->priority; break;
            case Terms::TICKET_URGENCY:       $fieldValue = $ticketModel->urgency; break;
            case Terms::TICKET_WORKFLOW:      $fieldValue = $ticketModel->workflow; break;
            case Terms::TICKET_LABELS:        $fieldValue = $ticketModel->labels; break;
            case Terms::TICKET_EMAIL_ACCOUNT: $fieldValue = $ticketModel->email_account; break;
            case Terms::TICKET_IS_HOLD:       $fieldValue = $ticketModel->is_hold; break;
            default: throw new \InvalidArgumentException('Unknown field');
        }

        return $this->checkValue($fieldValue, $operator, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case Terms::TICKET_ID:            $column = '{tickets}.id'; break;
            case Terms::TICKET_STATUS:        $column = '{tickets}.status'; break;
            case Terms::TICKET_DEPARTMENT:    $column = '{tickets}.department_id'; break;
            case Terms::TICKET_AGENT:         $column = '{tickets}.agent_id'; break;
            case Terms::TICKET_AGENT_TEAM:    $column = '{tickets}.agent_team_id'; break;

            case Terms::TICKET_LANGUAGE:      $column = '{tickets}.language_id'; break;
            case Terms::TICKET_PRODUCT:       $column = '{tickets}.product_id'; break;
            case Terms::TICKET_CATEGORY:      $column = '{tickets}.category_id'; break;
            case Terms::TICKET_PRIORITY:      $column = '{tickets}.priority_id'; break;
            case Terms::TICKET_URGENCY:       $column = '{tickets}.urgency'; break;
            case Terms::TICKET_WORKFLOW:      $column = '{tickets}.workflow_id'; break;
            case Terms::TICKET_EMAIL_ACCOUNT: $column = '{tickets}.email_account_id'; break;
            case Terms::TICKET_IS_HOLD:       $column = '{tickets}.is_hold'; break;
            default: $column                          = null;
        }

        if ($column) {
            return $this->checkValueQueryCondition($column, $operator, $options);
        }

        switch ($fieldId) {
            case Terms::TICKET_FOLLOWERS:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'tickets_participants', 'part', '{part}.ticket_id = {tickets}.id');

                return $this->checkValueQueryCondition('{part}.person_id', $operator, $options, $cond);

            case Terms::TICKET_LABELS:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'labels_tickets', 'label', '{label}.ticket_id = {tickets}.id');

                return $this->checkValueQueryCondition('{label}.label', $operator, $options, $cond);
        }

        throw new \InvalidArgumentException('Unknown field');
    }
}
