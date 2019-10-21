<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialRejectionResponse
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalOwnerPartialRejectionResponse extends TicketApprovalType
{
    protected $templateFile = 'emails_%s:ticket_approval_owner_partial_rejection_response.html.twig';
}
