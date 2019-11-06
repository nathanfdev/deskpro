<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalApproved
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalOwnerApproved extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_owner_approved.html.twig';
}
