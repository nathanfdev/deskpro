<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class AgentErrorUnknownFrom extends EmailBaseType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_agent:error_unknown_from.html.twig';

    public function __construct()
    {
    }

    public function getEventCodeType()
    {
        return 'error';
    }
}
