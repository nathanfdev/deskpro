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
use Orb\Auth\StateHandler\StateHandlerInterface;

class DeskproOAuth2Proxy extends AbstractCallbackAdatper
{

    /** @var DPOAuth2Proxy  */
    private $client;

    public function __construct( DPOAuth2Proxy $client )
    {
        $this->client = $client;
    }

    /**
     * Process the callback and return a final result.
     *
     *
     * @param array $callbackData
     * @param StateHandlerInterface $state
     *
     * @return \Orb\Auth\Result
     */
    protected function authenticateCallback( array $callbackData, StateHandlerInterface $state )
    {
        $token = null;
        if (!empty($callbackData)) {
            $token = $this->client->decodeToken($callbackData);
        }

        // missing or invalid token
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
     * Initialize the auth process by setting state, and returning a redirect result.
     *
     * @param StateHandlerInterface $state
     *
     * @return \Orb\Auth\Result
     */
    protected function authenticateInitialize( StateHandlerInterface $state )
    {
        $result = new Result(Result::REQUIRES_REDIRECT, null, [
            Result::MSG_REDIRECT => $this->client->buildEntrypointURL("<account>", "<provider>")
        ]);
        return $result;
    }
}
