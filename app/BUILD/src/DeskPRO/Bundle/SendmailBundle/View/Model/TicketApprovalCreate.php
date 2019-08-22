<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Class TicketApprovalCreate
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
class TicketApprovalCreate extends TicketApprovalType
{
    protected $templateFile = 'emails_common:ticket_approval_create.html.twig';
}
