<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialRejectionResponse
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalPartialRejectionResponse extends TicketApprovalType
{
    protected $templateFile = 'emails_common:ticket_approval_partial_rejection_response.html.twig';
}
