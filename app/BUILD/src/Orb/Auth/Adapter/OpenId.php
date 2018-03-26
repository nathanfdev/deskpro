<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;

class OpenId extends AbstractCallbackAdatper
{
    const OPTION_IDENTITY = 'identity';
    const OPTION_REALM    = 'realm';

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
                self::OPTION_REALM    => '',
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
        $openid            = new \LightOpenID($this->options->get(self::OPTION_REALM));
        $openid->identity  = $this->options->get(self::OPTION_IDENTITY);
        $openid->returnUrl = $this->getCallbackUrl();
        $openid->optional  = [
            'namePerson/friendly', 'contact/email', 'namePerson',
            'birthDate', 'person/gender', 'contact/country/home',
            'pref/language', 'pref/timezone',
        ];

        try {
            $result = new Result(Result::REQUIRES_REDIRECT, null, [Result::MSG_REDIRECT => $openid->authUrl()]);

            return $result;
        } catch (\ErrorException $e) {
            $result = new Result(Result::FAILURE_EXCEPTION, null, [Result::MSG_EXCEPTION => $e]);

            return $result;
        }
    }

    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        $openid = new \LightOpenID($this->options->get(self::OPTION_REALM));

        if (!$openid->validate()) {
            return new Result(Result::FAILURE, null, ['error_code' => 'invalid_validate', 'error_message' => 'Could not validate']);
        }

        $attributes = $openid->getAttributes();
        $userinfo   = [
            'first_name' => !empty($attributes['namePerson/friendly']) ? $attributes['namePerson/friendly'] : null,
            'email'      => !empty($attributes['contact/email']) ? $attributes['contact/email'] : null,
            'name'       => !empty($attributes['namePerson']) ? $attributes['namePerson'] : null,
            'birthday'   => !empty($attributes['birthDate']) ? $attributes['birthDate'] : null,
            'gender'     => !empty($attributes['person/gender']) ? $attributes['person/gender'] : null,
            'country'    => !empty($attributes['contact/country/home']) ? $attributes['contact/country/home'] : null,
            'language'   => !empty($attributes['pref/language']) ? $attributes['pref/language'] : null,
            'timezone'   => !empty($attributes['pref/timezone']) ? $attributes['pref/timezone'] : null,
        ];

        $identity = new \Orb\Auth\Identity($openid->identity, $userinfo);

        $result = new Result(Result::SUCCESS, $identity);

        return $result;
    }
}
