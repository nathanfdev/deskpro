<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalCreate
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalApproverCreate extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_approver_create.html.twig';
}
