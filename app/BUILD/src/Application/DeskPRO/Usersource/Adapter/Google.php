<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Google extends AbstractAdapter
{
    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'email'           => $info['user_email'],
            'email_confirmed' => true,
        ];
    }

    /**
     * @return \Orb\Auth\Adapter\Google
     */
    protected function _createAuthAdapterObject()
    {
        $hd = $this->usersource->getOption('apps_domain', null);

        return new \Orb\Auth\Adapter\Google($hd ?: null);
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_LOGIN_PULL_BTN,
            UsersourceInfo::CAPABILITY_WIDGET_OVERLAY_BTN,
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
