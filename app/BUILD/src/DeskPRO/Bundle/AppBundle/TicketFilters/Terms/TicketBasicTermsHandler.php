<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
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
            case Terms::TICKET_AGENT_TEAM:    $fieldValue = $ticketModel->agent; break;
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
}
