<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalCancel
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalOwnerCancel extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_owner_cancel.html.twig';
}
