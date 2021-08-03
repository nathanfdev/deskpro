<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class AgentWhitelistIp extends EmailBaseType
{
    use EventCodeEmailBaseType;

    /**
     * Link to whitelist ip page.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $url;

    /**
     * Ip Address.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $ip;

    protected $templateFile = 'emails_agent:whitelist_ip.html.twig';

    public function __construct($url, $ip)
    {
        $this->url = $url;
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getEventCodeType()
    {
        return 'login';
    }
}
