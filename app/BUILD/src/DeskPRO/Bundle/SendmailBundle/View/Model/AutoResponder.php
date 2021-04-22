<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class AutoResponder extends EmailBaseType
{
    protected $templateFile = 'emails_user:ticket_autoresponder.html.twig';
}
