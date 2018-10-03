<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Util\Urls;

class GooglePlus extends AbstractCallbackAdatper implements ExtraDetailsInterface
{
    /**
     * @var string
     */
    protected $cid;

    /**
     * @var string
     */
    protected $cs;

    /**
     * @var string only authenticate users if their email is of this domain
     */
    private $domain;

    public function __construct($cid, $cs, $domain)
    {
        $this->cid    = $cid;
        $this->cs     = $cs;
        $this->domain = $domain;
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateInitialize(StateHandlerInterface $state)
    {
        $client = $this->createClient();

        $result = new Result(Result::REQUIRES_REDIRECT, null, [Result::MSG_REDIRECT => $client->createAuthUrl()]);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        $client = $this->createClient();

        if (isset($_GET['code'])) {
            $client->authenticate($_GET['code']);

            if ($access_token = $client->getAccessToken()) {
                $attrs = $client->verifyIdToken();

                if ($this->domain) {
                    $domains    = explode(',', $this->domain);
                    $authorised = false;
                    foreach ($domains as $domain) {
                        if (Urls::verifyEmailDomain($attrs['email'], trim($domain))) {
                            $authorised = true;
                            break;
                        }
                    }
                    if (!$authorised) {
                        return new Result(
                            Result::FAILURE, null,
                            [
                                'error_code'    => 'invalid_argument',
                                'error_message' => 'email does not match specified domain',
                            ]
                        );
                    }
                }

                if (empty($attrs['sub'])) {
                    throw new \Exception('Google API payload was changed.');
                }

                $identity = new Identity(
                    $attrs['sub'],
                    [
                        'email'          => $attrs['email'],
                        'email_verified' => $attrs['email_verified'],
                        'sub'            => $attrs['sub'],
                    ]
                );
                $identity->setFriendlyIdentity($attrs['email']);

                return new Result(Result::SUCCESS, $identity);
            } else {
                return new Result(
                    Result::FAILURE, null,
                    ['error_code' => 'invalid_argument', 'error_message' => 'no code provided']
                );
            }
        }

        return new Result(
            Result::FAILURE, null, ['error_code' => 'invalid_argument', 'error_message' => 'no code provided']
        );
    }

    /**
     * @return \Google_Client
     */
    protected function createClient()
    {
        $client = new \Google_Client();
        $client->setClientId($this->cid);
        $client->setClientSecret($this->cs);
        $client->setRedirectUri($this->getCallbackUrl());
        $client->setScopes('email');

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraDetails()
    {
        return [
            'callback_url' => $this->getCallbackUrl(),
        ];
    }
}
