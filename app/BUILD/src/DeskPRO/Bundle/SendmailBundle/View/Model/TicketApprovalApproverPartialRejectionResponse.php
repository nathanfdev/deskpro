<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialRejectionResponse
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalApproverPartialRejectionResponse extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_approver_partial_rejection_response.html.twig';
}
