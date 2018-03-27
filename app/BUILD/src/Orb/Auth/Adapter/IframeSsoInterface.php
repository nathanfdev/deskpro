<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * Similar to JsSsoInterface, but without the code smells of it. This enforces the use of a hidden iFrame load on first
 * page load, so if that is all you need then use this. URL should do redirects and ultimately return JS that refreshed
 * the parent window if auth succeeds. Auth errors/popups/forms, etc should be hidden and not shown to user.
 */
interface IframeSsoInterface extends SsoLoginActionInterface
{
    /**
     * array of parameters that are passed to the _sso_iframe.html.twig template
     * Note: iframe_url is required.
     *
     * @param bool $is_first_page
     *
     * @return array of twig vars
     */
    public function getIframeTemplateParams($is_first_page);
}
