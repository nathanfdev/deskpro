<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialRejectionResponse
 */
class TicketApprovalOwnerPartialRejectionResponse extends TicketApprovalType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_%s:ticket_approval_owner_partial_rejection_response.html.twig';
}
