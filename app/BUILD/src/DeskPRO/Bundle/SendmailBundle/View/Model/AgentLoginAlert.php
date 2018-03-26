<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;

class AgentLoginAlert extends EmailBaseType
{
    /**
     * User IP.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $clientIp;

    /**
     * User browser User Agent.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $clientUserAgent;

    /**
     * User landing page.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $clientLandingPage;

    /**
     * User referring page.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $clientReferringPage;

    /**
     * First date seen.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $firstSeen;

    /**
     * Did they manage to connect?
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $success;

    protected $templateFile = 'emails_agent:login_alert.html.twig';

    /**
     * LoginAlert constructor.
     *
     * @param string $firstSeen
     * @param string $clientIp
     * @param string $clientUserAgent
     * @param string $clientLandingPage
     * @param string $clientReferringPage
     * @param $success
     *
     * @internal param Request $request
     */
    public function __construct($firstSeen, $clientIp, $clientUserAgent, $clientLandingPage, $clientReferringPage, $success)
    {
        $this->clientIp            = $clientIp;
        $this->clientUserAgent     = $clientUserAgent;
        $this->clientLandingPage   = $clientLandingPage;
        $this->clientReferringPage = $clientReferringPage;
        $this->firstSeen           = $firstSeen;
        $this->success             = (string) $success;
    }
}
