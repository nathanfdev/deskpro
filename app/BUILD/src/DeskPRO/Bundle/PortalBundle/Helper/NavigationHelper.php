<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NavigationHelper
{
    private $authorizationChecker;

    private $mask = null;

    private $routeMap = [
        0b00001 => 'portal_new_ticket',
        0b00010 => 'portal_kb',
        0b00100 => 'portal_news',
        0b01000 => 'portal_downloads',
        0b10000 => 'portal_feedback',
    ];

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function getMask()
    {
        if (null === $this->mask) {
            $tickets    = (int) $this->authorizationChecker->isGranted('USE_TICKETS');
            $articles   = ((int) $this->authorizationChecker->isGranted('USE_ARTICLES')) << 1;
            $news       = ((int) $this->authorizationChecker->isGranted('USE_NEWS')) << 2;
            $downloads  = ((int) $this->authorizationChecker->isGranted('USE_DOWNLOADS')) << 3;
            $feedback   = ((int) $this->authorizationChecker->isGranted('USE_FEEDBACK')) << 4;
            $this->mask = 0b00000 | $tickets | $articles | $news | $downloads | $feedback;
        }

        return $this->mask;
    }

    public function hasOnlyOneApp()
    {
        $mask = $this->getMask();
        // lets check the result is power of two
        return $mask && !($mask & ($mask - 1));
    }

    public function getRedirectRouteForOneApp()
    {
        $mask = $this->getMask();

        if ($this->hasOnlyOneApp() && isset($this->routeMap[$mask])) {
            return $this->routeMap[$mask];
        }

        return false;
    }

    public function hasNoActiveApps()
    {
        return $this->getMask() === 0;
    }
}
