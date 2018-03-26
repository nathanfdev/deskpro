<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Saml extends AbstractAdapter
{
    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'name'            => isset($info['name']) ? $info['name'] : '',
            'first_name'      => isset($info['first_name']) ? $info['first_name'] : '',
            'last_name'       => isset($info['last_name']) ? $info['last_name'] : '',
            'email'           => isset($info['email']) ? $info['email'] : '',
            'email_confirmed' => true,
        ];
    }

    /**
     * @return \Orb\Auth\Adapter\Saml
     */
    protected function _createAuthAdapterObject()
    {
        $options = $this->usersource->options;

        return new \Orb\Auth\Adapter\Saml($options);
    }

    public function getAgentLogoutRedirectUrl()
    {
        return '';
    }

    public function getUserLogoutRedirectUrl()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        $capabilities = [
            UsersourceInfo::CAPABILITY_SSO,
            UsersourceInfo::CAPABILITY_SSO_JS,
        ];

        if (isset($this->usersource->options['login_custom_text']) && $customButtonText = $this->usersource->options['login_custom_text']) {
            $capabilities[] = UsersourceInfo::CAPABILITY_LOGIN_TEXT_BTN;
        }

        return $capabilities;
    }
}
