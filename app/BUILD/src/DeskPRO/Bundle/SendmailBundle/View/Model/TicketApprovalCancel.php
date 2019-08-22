<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalCancel
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalCancel extends TicketApprovalType
{
    protected $templateFile = 'emails_common:ticket_approval_cancel.html.twig';
}
