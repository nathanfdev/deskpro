<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace deskpro_magento\Usersource\Adapter;

use Application\DeskPRO\App;
use Application\DeskPRO\Usersource\Adapter\AbstractAdapter;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Magento extends AbstractAdapter
{
    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return array(
            'name'             => isset($info['name']) ? $info['name'] : '',
            'first_name'       => isset($info['first_name']) ? $info['first_name'] : '',
            'last_name'        => isset($info['last_name']) ? $info['last_name'] : '',
            'email'            => isset($info['email_address']) ? $info['email_address'] : '',
            'email_confirmed'  => true,
        );
    }

    /**
     * @return \deskpro_magento\Usersource\Auth\Magento
     */
    protected function _createAuthAdapterObject()
    {
        $options = $this->usersource->options;
        $options['url'] = App::getSetting("Magento.url");
        $options['api_user'] = App::getSetting("Magento.api_user");
        $options['api_key'] = App::getSetting("Magento.api_key");

        return new \deskpro_magento\Usersource\Auth\Magento($options);
    }

    /**
     * Find a user identity just by an email address.
     *
     * @param $id_input
     * @return \Orb\Auth\Identity|null
     */
    public function findIdentityByInput($id_input)
    {
        $adapter = $this->getAuthAdapter();

        $userinfo = $adapter->getUserInfoForEmail($id_input);
        if (!$userinfo) {
            return null;
        }

        return $adapter->getIdentityFromUserInfo($userinfo);
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            UsersourceInfo::CAPABILITY_FORM_LOGIN,
            UsersourceInfo::CAPABILITY_GET_USER_INFO,
            UsersourceInfo::CAPABILITY_FIND_IDENTITY,
            UsersourceInfo::CAPABILITY_COOKIE_LOGIN,
            UsersourceInfo::CAPABILITY_SSO_JS
        );
    }
}
