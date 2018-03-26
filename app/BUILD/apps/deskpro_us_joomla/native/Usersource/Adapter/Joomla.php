<?php

/**
 * DeskPRO.
 */

namespace deskpro_us_joomla\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Joomla extends \Application\DeskPRO\Usersource\Adapter\AbstractAdapter
{
    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'name'            => isset($info['name']) ? $info['name'] : '',
            'email'           => isset($info['email']) ? $info['email'] : '',
            'username'        => isset($info['username']) ? $info['username'] : '',
            'email_confirmed' => true,
        ];
    }

    /**
     * @return \Joomla\Usersource\Auth\Joomla
     */
    protected function _createAuthAdapterObject()
    {
        $options = $this->usersource->options;

        return new \deskpro_us_joomla\Usersource\Auth\Joomla($options);
    }

    /**
     * Find a user identity just by an email address.
     *
     * @param $id_input
     *
     * @return \Orb\Auth\Identity|null
     */
    public function findIdentityByInput($id_input)
    {
        $adapter = $this->getAuthAdapter();

        $userinfo = $adapter->getUserInfoForEmail($id_input);
        if (!$userinfo) {
            return;
        }

        return $adapter->getIdentityFromUserInfo($userinfo);
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_FORM_LOGIN,
            UsersourceInfo::CAPABILITY_GET_USER_INFO,
            UsersourceInfo::CAPABILITY_FIND_IDENTITY,
            UsersourceInfo::CAPABILITY_SHARE_SESSION,
        ];
    }
}
