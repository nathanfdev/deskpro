<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * Adapters that can authenticate via cookies to allow single sign on.
 */
interface CookieLoginInterface extends AdapterInterface
{
    /**
     * Authenticates based on data a cookie (such as a session or remember me cookie).
     *
     * @param array $cookies
     *
     * @return array|false Array of user info or false if nothing can be found
     */
    public function authenticateCookie(array $cookies);
}
