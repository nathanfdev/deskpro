<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class RateLimitNotice extends EmailBaseType
{
    protected $templateFile = 'emails_user:rate_limit_notice.html.twig';

    public function __construct()
    {
    }
}
