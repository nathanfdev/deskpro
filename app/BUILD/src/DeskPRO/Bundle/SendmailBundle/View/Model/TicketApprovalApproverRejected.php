<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalRejected
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalApproverRejected extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_approver_rejected.html.twig';
}
