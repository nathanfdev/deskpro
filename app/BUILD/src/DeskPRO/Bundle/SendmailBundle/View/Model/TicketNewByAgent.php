<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class TicketNewByAgent extends TicketEmailType
{
    protected $templateFile = 'emails_user:ticket_new_by_agent.html.twig';
}
