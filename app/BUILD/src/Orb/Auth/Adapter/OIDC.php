<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Jumbojett\OpenIDConnectClient;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;

class OIDC extends AbstractCallbackAdatper
{
    const OPTION_IDENTITY = 'identity';
    const OPTION_PROVIDER = 'provider';
    const OPTION_CLIENTID = 'clientid';
    const OPTION_SECRET   = 'secret';

    /**
     * @var \Orb\Util\OptionsArray
     */
    protected $options;

    public function __construct(array $options)
    {
        $this->initOptions();
        $this->options->setArray($options);
    }

    protected function initOptions()
    {
        $this->options = new \Orb\Util\OptionsArray(
            [
                self::OPTION_IDENTITY => '',
                self::OPTION_PROVIDER => '',
                self::OPTION_CLIENTID => '',
                self::OPTION_SECRET   => '',
            ]
        );
    }

    /**
     * Initialize the auth process by setting state, and returning a redirect result.
     *
     * @return \Orb\Auth\Result
     */
    protected function authenticateInitialize(StateHandlerInterface $state)
    {
        $oidc = new OpenIDConnectClient(
            $this->options->get(self::OPTION_PROVIDER),
            $this->options->get(self::OPTION_CLIENTID),
            $this->options->get(self::OPTION_SECRET)
        );
        $oidc->setRedirectURL($this->getCallbackUrl());
        $oidc->addScope(['openid', 'profile', 'email', 'phone', 'address']);
        $oidc->authenticate();
    }

    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        $oidc = new OpenIDConnectClient(
            $this->options->get(self::OPTION_PROVIDER),
            $this->options->get(self::OPTION_CLIENTID),
            $this->options->get(self::OPTION_SECRET)
        );
        $oidc->addScope(['openid', 'profile', 'email', 'phone', 'address']);
        $oidc->authenticate();
        $oidcUserinfo = $oidc->requestUserInfo();
        $userinfo     = [
            'first_name' => isset($oidcUserinfo['given_name']) ? $oidcUserinfo['given_name'] : null,
            'email'      => isset($oidcUserinfo['email']) ? $oidcUserinfo['email'] : null,
            'name'       => isset($oidcUserinfo['name']) ? $oidcUserinfo['name'] : null,
            'birthday'   => isset($oidcUserinfo['birthday']) ? $oidcUserinfo['birthday'] : null,
            'gender'     => isset($oidcUserinfo['gender']) ? $oidcUserinfo['gender'] : null,
            'country'    => isset($oidcUserinfo['given_name']) ? $oidcUserinfo['given_name'] : null,
            'language'   => isset($oidcUserinfo['locale']) ? $oidcUserinfo['locale'] : null,
            'timezone'   => isset($oidcUserinfo['zoneinfo']) ? $oidcUserinfo['zoneinfo'] : null,
        ];

        $identity = new \Orb\Auth\Identity($this->options->get(self::OPTION_IDENTITY), $userinfo);

        $result = new Result(Result::SUCCESS, $identity);

        return $result;
    }
}
