<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlCondition;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\CheckValueUtils;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\SqlQueryUtils;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;

class TicketBasicTermsHandler extends AbstractTermsHandler
{
    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            TermFieldIds::TICKET_ID,

            // org id in here because only one supproted now, when adding other org fields
            // should move this into that handler instead to keep consistency
            TermFieldIds::ORG_ID,

            TermFieldIds::TICKET_STATUS,
            TermFieldIds::TICKET_DEPARTMENT,
            TermFieldIds::TICKET_AGENT,
            TermFieldIds::TICKET_AGENT_TEAM,
            TermFieldIds::TICKET_FOLLOWERS,
            TermFieldIds::TICKET_LANGUAGE,
            TermFieldIds::TICKET_PRODUCT,
            TermFieldIds::TICKET_CATEGORY,
            TermFieldIds::TICKET_PRIORITY,
            TermFieldIds::TICKET_URGENCY,
            TermFieldIds::TICKET_WORKFLOW,
            TermFieldIds::TICKET_LABELS,
            TermFieldIds::TICKET_EMAIL_ACCOUNT,
            TermFieldIds::TICKET_IS_HOLD,
            TermFieldIds::TICKET_PROBLEM_ID,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::TICKET_ID:            $fieldValue = $ticketModel->id; break;
            case TermFieldIds::ORG_ID:               $fieldValue = $ticketModel->organization->id; break;
            case TermFieldIds::TICKET_STATUS:        $fieldValue = $ticketModel->status; break;
            case TermFieldIds::TICKET_DEPARTMENT:    $fieldValue = $ticketModel->department; break;
            case TermFieldIds::TICKET_AGENT:         $fieldValue = $ticketModel->agent; break;
            case TermFieldIds::TICKET_AGENT_TEAM:    $fieldValue = $ticketModel->agent_team; break;
            case TermFieldIds::TICKET_FOLLOWERS:     $fieldValue = $ticketModel->followers; break;
            case TermFieldIds::TICKET_LANGUAGE:      $fieldValue = $ticketModel->language; break;
            case TermFieldIds::TICKET_PRODUCT:       $fieldValue = $ticketModel->product; break;
            case TermFieldIds::TICKET_CATEGORY:      $fieldValue = $ticketModel->category; break;
            case TermFieldIds::TICKET_PRIORITY:      $fieldValue = $ticketModel->priority; break;
            case TermFieldIds::TICKET_URGENCY:       $fieldValue = $ticketModel->urgency; break;
            case TermFieldIds::TICKET_WORKFLOW:      $fieldValue = $ticketModel->workflow; break;
            case TermFieldIds::TICKET_LABELS:        $fieldValue = $ticketModel->labels; break;
            case TermFieldIds::TICKET_EMAIL_ACCOUNT: $fieldValue = $ticketModel->email_account; break;
            case TermFieldIds::TICKET_IS_HOLD:       $fieldValue = $ticketModel->is_hold; break;
            case TermFieldIds::TICKET_PROBLEM_ID:    return false;
            default: throw new \InvalidArgumentException('Unknown field');
        }

        return CheckValueUtils::checkValue($fieldValue, $operator, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::TICKET_ID:            $column = '{tickets}.id'; break;
            case TermFieldIds::ORG_ID:               $column = '{tickets}.organization_id'; break;
            case TermFieldIds::TICKET_STATUS:        $column = '{tickets}.status'; break;
            case TermFieldIds::TICKET_DEPARTMENT:    $column = '{tickets}.department_id'; break;
            case TermFieldIds::TICKET_AGENT:         $column = '{tickets}.agent_id'; break;
            case TermFieldIds::TICKET_AGENT_TEAM:    $column = '{tickets}.agent_team_id'; break;

            case TermFieldIds::TICKET_LANGUAGE:      $column = '{tickets}.language_id'; break;
            case TermFieldIds::TICKET_PRODUCT:       $column = '{tickets}.product_id'; break;
            case TermFieldIds::TICKET_CATEGORY:      $column = '{tickets}.category_id'; break;
            case TermFieldIds::TICKET_PRIORITY:      $column = '{tickets}.priority_id'; break;
            case TermFieldIds::TICKET_URGENCY:       $column = '{tickets}.urgency'; break;
            case TermFieldIds::TICKET_WORKFLOW:      $column = '{tickets}.workflow_id'; break;
            case TermFieldIds::TICKET_EMAIL_ACCOUNT: $column = '{tickets}.email_account_id'; break;
            case TermFieldIds::TICKET_IS_HOLD:       $column = '{tickets}.is_hold'; break;
            default: $column                                 = null;
        }

        if ($column) {
            return SqlQueryUtils::buildQueryCondition($column, $operator, $options);
        }

        switch ($fieldId) {
            case TermFieldIds::TICKET_FOLLOWERS:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'tickets_participants', 'part', '{part}.ticket_id = {tickets}.id');

                return SqlQueryUtils::buildQueryCondition('{part}.person_id', $operator, $options, $cond);

            case TermFieldIds::TICKET_LABELS:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'labels_tickets', 'label', '{label}.ticket_id = {tickets}.id');

                return SqlQueryUtils::buildQueryCondition('{label}.label', $operator, $options, $cond);

            case TermFieldIds::TICKET_PROBLEM_ID:
                $cond = new SqlCondition();
                $cond->addUniqueJoin('tickets', 'problem2tickets', 'prob', '{prob}.ticket_id = {tickets}.id');

                return SqlQueryUtils::buildQueryCondition('{prob}.problem_id', $operator, $options, $cond);
        }

        throw new \InvalidArgumentException('Unknown field');
    }
}
