<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalApproved
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalApproved extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_approved.html.twig';
}
