<?php

/**
 * DeskPRO.
 */

namespace deskpro_us_jwt\Usersource\Adapter;

use Application\DeskPRO\Usersource\Adapter\AbstractAdapter;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Jwt extends AbstractAdapter
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
     * @return \deskpro_magento\Usersource\Auth\Magento
     */
    protected function _createAuthAdapterObject()
    {
        $options = $this->usersource->options;

        return new \deskpro_us_jwt\Usersource\Auth\Jwt($options);
    }

    public function getAgentLogoutRedirectUrl()
    {
        return isset($this->usersource->options['logout_agent_url']) ? $this->usersource->options['logout_agent_url'] : '';
    }

    public function getUserLogoutRedirectUrl()
    {
        return isset($this->usersource->options['logout_user_url']) ? $this->usersource->options['logout_user_url'] : '';
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

        if ($custom_button_text = $this->usersource->options['login_custom_text']) {
            $capabilities[] = UsersourceInfo::CAPABILITY_LOGIN_TEXT_BTN;
            $capabilities[] = UsersourceInfo::CAPABILITY_WIDGET_OVERLAY_BTN;
        }

        return $capabilities;
    }
}
