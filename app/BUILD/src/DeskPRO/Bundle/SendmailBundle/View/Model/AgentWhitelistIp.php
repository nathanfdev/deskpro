<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class AgentWhitelistIp extends EmailBaseType
{
    /**
     * Link to whitelist ip page.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $url;

    protected $templateFile = 'emails_agent:whitelist_ip.html.twig';

    public function __construct($url)
    {
        $this->url = $url;
    }
}
