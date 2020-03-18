<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialApprovalResponse
 */
class TicketApprovalApproverPartialApprovalResponse extends TicketApprovalType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_%s:ticket_approval_approver_partial_approval_response.html.twig';
}
