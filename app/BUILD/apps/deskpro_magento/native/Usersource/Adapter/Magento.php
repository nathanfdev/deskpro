<?php

/**
 * DeskPRO.
 */

namespace deskpro_magento\Usersource\Adapter;

use Application\DeskPRO\Usersource\Adapter\AbstractAdapter;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Magento extends AbstractAdapter
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
        return new \deskpro_magento\Usersource\Auth\Magento($this->usersource->options);
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
            UsersourceInfo::CAPABILITY_COOKIE_LOGIN,
            UsersourceInfo::CAPABILITY_SSO_JS,
        ];
    }
}
