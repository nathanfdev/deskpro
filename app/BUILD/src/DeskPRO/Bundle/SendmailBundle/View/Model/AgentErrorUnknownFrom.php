<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class AgentErrorUnknownFrom extends EmailBaseType
{
    protected $templateFile = 'emails_agent:error_unknown_from.html.twig';

    public function __construct()
    {
    }
}
