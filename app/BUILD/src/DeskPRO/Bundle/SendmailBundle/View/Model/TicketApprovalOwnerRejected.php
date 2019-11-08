<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalRejected
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalOwnerRejected extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_owner_rejected.html.twig';
}
