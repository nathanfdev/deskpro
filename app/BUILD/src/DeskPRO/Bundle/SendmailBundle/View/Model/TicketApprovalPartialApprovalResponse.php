<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalPartialApprovalResponse
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalPartialApprovalResponse extends TicketApprovalType
{
    protected $templateFile = 'emails_common:ticket_approval_partial_approval_response.html.twig';
}
