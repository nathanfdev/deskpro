<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalCancel
 */
class TicketApprovalOwnerCancel extends TicketApprovalType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_%s:ticket_approval_owner_cancel.html.twig';
}
