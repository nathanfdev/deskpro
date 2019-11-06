<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalCreate
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalOwnerCreate extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_owner_create.html.twig';
}
