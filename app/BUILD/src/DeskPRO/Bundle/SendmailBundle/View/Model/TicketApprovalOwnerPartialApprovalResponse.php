<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialApprovalResponse
 */
class TicketApprovalOwnerPartialApprovalResponse extends TicketApprovalType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_%s:ticket_approval_owner_partial_approval_response.html.twig';
}
