<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * SsoCapable adapters adhere to the CallbackInterface, but also need to respect the single sign-off by providing
 * a logout URL. If this SSO adapter does redirects for authentication, you probably want to use this.
 */
interface SsoCapableInterface extends CallbackInterface
{
    /**
     * URL we send the deskpro user to after they log out of our system
     * This is to comply with sing sign-off in SAML and our JWT system, but is useful in any SSO implementation.
     *
     * @return string
     */
    public function getLogoutRedirectUrl();

    /**
     * Allow external processes to determine and set the logout URL if needed. Should override any internal logic for
     * logout URL.
     */
    public function setLogoutRedirectUrl($url);
}
