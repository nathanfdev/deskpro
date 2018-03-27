<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

/**
 * stuff that is not meant to goto the callback interface, but instead to use the sso response action.
 */
interface SsoLoginActionInterface
{
    const CONTEXT_BACKGROUND                 = 'background';
    const CONTEXT_REDIRECT                   = 'redirect';
    const TOKEN_ATTRIBUTE_BACKGROUND_REFRESH = 'background_refresh';

    /**
     * TODO: Depending on a controller is odd, this should be cleaned up eventually.
     * TODO: no current implementers actually use this $controller arg. should replace with $request instead.
     *
     *
     * @param \Application\DeskPRO\Controller\AbstractController $controller
     *
     * @return \Orb\Auth\Result
     */
    public function getSsoLoginActionResult(\Application\DeskPRO\Controller\AbstractController $controller = null);

    /**
     * Return true if the user is authenticated via a background js (iframe) and you want to signal that
     * it should be handled on success as just a simple refresh of parent page.
     *
     * FALSE means the "return" get param redirect, or redirect to interface main page will be loaded instead of
     * the simple refresh JS page.
     *
     * @return bool
     */
    public function isBackgroundSsoSimpleRefresh();
}
