<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class GooglePlus extends AbstractAdapter
{
    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'email'           => $info['email'],
            'email_confirmed' => $info['email_verified'],
        ];
    }

    /**
     * @return \Orb\Auth\Adapter\GooglePlus
     */
    protected function _createAuthAdapterObject()
    {
        $cid    = $this->usersource->getOption('client_id', null);
        $cs     = $this->usersource->getOption('client_secret', null);
        $domain = $this->usersource->getOption('google_apps_domain', null);

        return new \Orb\Auth\Adapter\GooglePlus($cid, $cs, $domain);
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_LOGIN_PULL_BTN,        ];
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
