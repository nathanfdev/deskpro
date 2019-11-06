<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialApprovalResponse
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalApproverPartialApprovalResponse extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_approver_partial_approval_response.html.twig';
}
