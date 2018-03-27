<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class GatewayAutoresponseWarn extends EmailBaseType
{
    protected $templateFile = 'emails_user:gateway_autoresponse_warn.html.twig';

    public function __construct()
    {
    }
}
