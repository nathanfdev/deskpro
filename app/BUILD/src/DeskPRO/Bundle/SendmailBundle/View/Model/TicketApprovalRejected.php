<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalRejected
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalRejected extends TicketApprovalType
{
    protected $templateFile = 'emails_common:ticket_approval_rejected.html.twig';
}
