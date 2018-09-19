<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\DPOAuth2Proxy;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Log\Logger;
use Orb\Log\Loggable;

class DeskproOAuth2Proxy extends PluginAdapter implements CallbackInterface, Loggable
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * If in callback context, then an array of callback data.
     *
     * @var array
     */
    private $callbackData = null;

    /**
     * The callback URL. This is not used at the moment
     *
     * @var string
     */
    private $callbackUrl = null;

    /** @var DPOAuth2Proxy  */
    private $client;

    public function __construct( DPOAuth2Proxy $client )
    {
        $this->client = $client;
    }

    /**
     * Switches the adapter to the callback context using form data $data.
     *
     * @param array $data Form data or other callback data
     */
    public function setCallbackContext(array $data)
    {
        $this->callbackData = $data;
    }

    /**
     * Set the URL the user is returned to.
     *
     * @param string $url
     */
    public function setCallbackUrl($url)
    {
        $this->callbackUrl = $url;
    }

    /**
     * Get the callback URL.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        if (!$this->callbackUrl) {
            throw new \RuntimeException('No callback URL was set');
        }

        return $this->callbackUrl;
    }

    /**
     * @return Result
     */
    public function doAuthenticate()
    {
        // missing or invalid token
        $token = $this->client->decodeToken($this->callbackData);
        if (empty($token)) {
            return new Result(Result::FAILURE_INVALID_CREDS);
        }

        $identity = new Identity(
            $token["email"],
            [
                'email'          => $token["email"],
                'email_verified' => $token["email"]
            ]
        );
        $identity->setFriendlyIdentity($token["email"]);
        return new Result(Result::SUCCESS, $identity);
    }

    /**
     * Set the logger.
     *
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
