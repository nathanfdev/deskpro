<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class NewTicketGuest extends TicketEmailType
{
    protected $templateFile = 'emails_user:new_ticket_guest.html.twig';
}
