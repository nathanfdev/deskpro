<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialRejectionResponse
 */
class TicketApprovalApproverPartialRejectionResponse extends TicketApprovalType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_%s:ticket_approval_approver_partial_rejection_response.html.twig';
}
