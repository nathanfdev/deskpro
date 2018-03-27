<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * Saml adapter needs this one for the factory to create it properly. These URLs come from outside of the auth system
 * itself, and are injected in as data for this adapter to use.
 */
interface SamlAdapterInterface
{
    const CONTEXT_SAML_REDIRECT_BACKGROUND = 'auth-to-iframe';

    public function setMetadataXmlUrl($url);
    public function getMetadataXmlUrl();

    public function setSingleLogoutServiceUrl($url);
    public function getSingleLogoutServiceUrl();

    /**
     * Return a response to send to browser OR do the redirect yourself inside the method (and exit).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function performSingleLogOutService();

    /**
     * Return a response to send to browser.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMetadataXmlResponse();
}
