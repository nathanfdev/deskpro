<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Facebook extends AbstractAdapter
{
    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'name'            => isset($info['name']) ? $info['name'] : '',
            'first_name'      => isset($info['first_name']) ? $info['first_name'] : '',
            'last_name'       => isset($info['last_name']) ? $info['last_name'] : '',
            'email'           => isset($info['email']) ? $info['email'] : '',
            'email_confirmed' => isset($info['verified']) ? $info['verified'] : true,
        ];
    }

    /**
     * @return \Orb\Auth\Adapter\Facebook
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\Facebook(
            $this->usersource->getOption('app_key'),
            $this->usersource->getOption('app_secret')
        );
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_LOGIN_PULL_BTN,
            UsersourceInfo::CAPABILITY_WIDGET_OVERLAY_BTN,
            UsersourceInfo::CAPABILITY_NEW_COMMENT_TAB,
        ];
    }

    /**
     * @param mixed $capability
     *
     * @return bool
     */
    public function isCapable($capability)
    {
        return in_array($capability, $this->getCapabilities());
    }
}
