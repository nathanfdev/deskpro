<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalCancel
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalApproverCancel extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_approver_cancel.html.twig';
}
