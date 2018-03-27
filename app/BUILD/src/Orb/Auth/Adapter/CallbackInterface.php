<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * Adapters that use a two-step authentication scheme with a callback (such as OpenID)
 * should implement this interface.
 *
 * When the callback page is called up, the context is set which should change the operations
 * used in the authenticate() method of AdapterInterface.
 */
interface CallbackInterface extends AdapterInterface
{
    /**
     * Switches the adapter to the callback context using form data $data.
     *
     * @param array $data Form data or other callback data
     */
    public function setCallbackContext(array $data);

    /**
     * Set the callback URL the user should return to when the remote service is finished.
     *
     * @param string $url The URL
     */
    public function setCallbackUrl($url);

    /**
     * @return string
     */
    public function getCallbackUrl();
}
