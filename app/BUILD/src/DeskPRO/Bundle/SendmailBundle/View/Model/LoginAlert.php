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

use JMS\Serializer\Annotation as JMS;

class LoginAlert extends EmailBaseType
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

    protected $templateFile = 'emails_user:login_alert.html.twig';

    /**
     * LoginAlert constructor.
     *
     * @param string $clientIp
     * @param string $clientUserAgent
     * @param string $clientLandingPage
     * @param string $clientReferringPage
     * @param string $firstSeen
     * @param bool   $success
     */
    public function __construct($clientIp, $clientUserAgent, $clientLandingPage, $clientReferringPage, $firstSeen, $success)
    {
        $this->clientIp            = $clientIp;
        $this->clientUserAgent     = $clientUserAgent;
        $this->clientLandingPage   = $clientLandingPage;
        $this->clientReferringPage = $clientReferringPage;
        $this->firstSeen           = $firstSeen;
        $this->success             = (string) $success;
    }
}
