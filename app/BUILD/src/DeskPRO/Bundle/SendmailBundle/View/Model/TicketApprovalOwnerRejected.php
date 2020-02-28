<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalRejected
 */
class TicketApprovalOwnerRejected extends TicketApprovalType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_%s:ticket_approval_owner_rejected.html.twig';
}
