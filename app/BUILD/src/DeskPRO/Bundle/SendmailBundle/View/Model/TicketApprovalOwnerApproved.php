<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalApproved
 */
class TicketApprovalOwnerApproved extends TicketApprovalType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_%s:ticket_approval_owner_approved.html.twig';
}
