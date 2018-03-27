<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class TicketAutocloseWarn extends TicketEmailType
{
    protected $templateFile = 'emails_user:ticket_autoclose_warn.html.twig';
}
