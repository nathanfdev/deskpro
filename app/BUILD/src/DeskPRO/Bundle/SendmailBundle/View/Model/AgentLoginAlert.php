<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DateTime;
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

    protected static $templateFile = 'emails_agent:login_alert.html.twig';

    /**
     * LoginAlert constructor.
     *
     * @param Request  $request
     * @param DateTime $firstSeen
     * @param $success
     */
    public function __construct(Request $request, DateTime $firstSeen, $success)
    {
        $this->clientIp            = $request->getClientIp();
        $this->clientUserAgent     = $request->headers->get('User-Agent');
        $this->clientLandingPage   = $request->getRequestUri();
        $this->clientReferringPage = $request->headers->get('Referer');
        $this->firstSeen           = $firstSeen->format('D, jS M Y g:ia');
        $this->success             = (string) $success;
    }
}
