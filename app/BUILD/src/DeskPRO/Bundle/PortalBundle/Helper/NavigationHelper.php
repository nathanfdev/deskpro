<?php

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
