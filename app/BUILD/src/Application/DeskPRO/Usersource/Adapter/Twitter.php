<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Twitter extends AbstractAdapter
{
    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'name'    => $info['fullname'] ?: $info['identity_friendly'],
            'twitter' => [
                'screen_name'        => $info['identity_friendly'],
                'user_id'            => $info['identity'],
                'oauth_token'        => $info['access_token'],
                'oauth_token_secret' => $info['access_token_secret'],
            ],
        ];
    }

    public function getDisplayName(array $info)
    {
        return '@'.$info['identity_friendly'];
    }

    public function getDisplayLink(array $info)
    {
        return 'htpt://twitter.com/'.$info['identity_friendly'];
    }

    /**
     * @return \Orb\Auth\Adapter\Twitter
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\Twitter(
            $this->usersource->getOption('consumer_key') ?: $this->usersource->getOption('app_key'),
            $this->usersource->getOption('consumer_secret') ?: $this->usersource->getOption('app_secret')
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
