<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalApproved
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalApproverApproved extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_approver_approved.html.twig';
}
