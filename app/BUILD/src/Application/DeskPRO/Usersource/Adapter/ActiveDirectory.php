<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\App;
use Application\DeskPRO\Ldap\Ldap;
use Application\DeskPRO\Ldap\LdapPagedSearcher;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;
use Orb\Util\Arrays;

class ActiveDirectory extends AbstractAdapter
{
    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'name'            => isset($info['name']) ? $info['name'] : '',
            'first_name'      => isset($info['first_name']) ? $info['first_name'] : '',
            'last_name'       => isset($info['last_name']) ? $info['last_name'] : '',
            'email'           => isset($info['email_address']) ? $info['email_address'] : '',
            'email_confirmed' => true,
            'picture_data'    => isset($info['picture_data']) ? $info['picture_data'] : null,
            'phone'           => isset($info['phone']) ? $info['phone'] : null,
        ];
    }

    /**
     * @return \Orb\Auth\Adapter\ActiveDirectory
     */
    protected function _createAuthAdapterObject()
    {
        $options = array_merge($this->usersource->options, ['ldapClass' => Ldap::class]);
        $adapter = new \Orb\Auth\Adapter\ActiveDirectory($options);
        $appEnv  = App::$container->get('deskpro.app_env');

        if (($appEnv->isDebug() || $appEnv->getConfig('logs.enable_usersource_log')) && $adapter instanceof \Orb\Log\Loggable) {
            $logger = App::$container->getUsersourceLogger();
            $adapter->setLogger($logger);
        }

        return $adapter;
    }

    /**
     * @return LdapPagedSearcher
     */
    public function findAllRecords()
    {
        $usersource = clone $this->usersource;
        $usersource->setOption('bindRequiresDn', true);

        /** @var \Orb\Auth\Adapter\ActiveDirectory $adapter */
        $adapter = $usersource->getAdapter()->getAuthAdapter();

        if ($adapter->getLogger()) {
            $adapter->getLogger()->logDebug('findAllIdentities');
        }

        $disable_paging = $usersource->getOption('disableLdapPaging', false);
        $paging         = !$disable_paging;
        $size           = $usersource->getOption('ldapPerPage', 1000);

        return $adapter->findAllRecords($size, $paging);
    }

    public function getIdentityForDn($dn)
    {
        $usersource = clone $this->usersource;
        $usersource->setOption('bindRequiresDn', true);

        /** @var \Orb\Auth\Adapter\ActiveDirectory $adapter */
        $adapter = $usersource->getAdapter()->getAuthAdapter();

        if ($adapter->getLogger()) {
            $adapter->getLogger()->logDebug('getIdentityForDn');
        }

        return $adapter->getIdentityForDn($dn);
    }

    /**
     * Find a user identity just by an email address.
     *
     * @param string $id_input Username or email address
     *
     * @return \Orb\Auth\Identity|null
     */
    public function findIdentityByInput($id_input)
    {
        $usersource = clone $this->usersource;
        $usersource->setOption('bindRequiresDn', true);

        /** @var \Orb\Auth\Adapter\ActiveDirectory $adapter */
        $adapter = $usersource->getAdapter()->getAuthAdapter();

        if ($adapter->getLogger()) {
            $adapter->getLogger()->logDebug("findIdentityByInput: $id_input");
        }

        $adapter->setFormData([
            'username' => $id_input,
            'password' => '',
        ]);
        $rec = null;

        try {
            $rec_arr = $adapter->findRecordViaEmail($id_input);
            if (!$rec_arr || !isset($rec_arr['dn'])) {
                $rec_arr = $adapter->findRecordViaUsername($id_input);
            }
            if (!$rec_arr || !isset($rec_arr['dn'])) {
                $rec_arr = $adapter->findRecordViaDn($id_input);
            }
        } catch (\Exception $e) {
            if ($adapter->getLogger()) {
                $adapter->getLogger()->logDebug("findIdentityByInput Exception: {$e->getCode()} {$e->getMessage()}");
            }
            throw $e;
        }

        $raw_info = [];
        if ($rec_arr && isset($rec_arr['dn'])) {
            if ($adapter->getLogger()) {
                $adapter->getLogger()->logDebug('findRecordViaEmail result: '.print_r($rec_arr, 1));
            }

            $raw_info = $rec_arr;

            if (!empty($raw_info['samaccountname'])) {
                $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['samaccountname']);
            } elseif (!empty($raw_info['uid'])) {
                $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['uid']);
            }
            if (!empty($raw_info['distinguishedname'])) {
                $raw_info['identity'] = Arrays::getFirstItem($raw_info['distinguishedname']);
            } else {
                $raw_info['identity'] = Arrays::getFirstItem($raw_info['dn']);
            }

            $auth = $this->getAuthAdapter()->getZendAuthAdapter();

            // Bogus because zend only creates ldap obj when its needed,
            // so this is a hack to get it to set all the correct options
            // for us
            try {
                $auth->setUsername('__bogus__');
                $auth->setPassword('__bogus__');
                $auth->authenticate();
            } catch (\Exception $e) {
            }

            /** @var $ldap \Zend\Ldap\Ldap */
            $ldap = $auth->getLdap();

            /** @var $rec \Zend\Ldap\Node */
            $rec = $ldap->getNode($rec_arr['dn']);

            if ($adapter->getLogger()) {
                $adapter->getLogger()->logDebug('getNode result: '.print_r($rec, 1));
            }
        } else {
            if ($adapter->getLogger()) {
                $adapter->getLogger()->logDebug('findRecordViaEmail result: null');
            }
        }

        if ($rec) {
            foreach ($rec->getData() as $name => $value) {
                try {
                    $raw_info[$name] = $rec->getAttribute($name, null);
                } catch (\Exception $e) {
                    $raw_info[$name] = $value;
                }
            }

            if ($rec->getAttribute('givenName')) {
                $raw_info['first_name'] = $rec->getAttribute('givenName', 0);
            }
            if ($rec->getAttribute('sn')) {
                $raw_info['last_name'] = $rec->getAttribute('sn', 0);
            }

            if (isset($raw_info['first_name']) && isset($raw_info['last_name'])) {
                $raw_info['name'] = $raw_info['first_name'].' '.$raw_info['last_name'];
            } elseif ($rec->getAttribute('name')) {
                $raw_info['name'] = $rec->getAttribute('name', 0);
            } elseif ($rec->getAttribute('cn')) {
                $raw_info['name'] = $rec->getAttribute('cn', 0);
            }

            if ($rec->getAttribute('mail')) {
                $raw_info['email_address'] = $rec->getAttribute('mail', 0);
            } elseif (\Orb\Validator\StringEmail::isValueValid($rec->getAttribute('userPrincipalName', 0))) {
                $raw_info['email_address'] = $rec->getAttribute('userPrincipalName', 0);
            }

            if ($rec->getAttribute('jpegPhoto')) {
                $raw_info['picture_data'] = $rec->getAttribute('jpegPhoto', 0);
            } elseif ($rec->getAttribute('thumbnailPhoto')) {
                $raw_info['picture_data'] = $rec->getAttribute('thumbnailPhoto', 0);
            }

            if ($rec->getAttribute('telephoneNumber')) {
                $raw_info['phone'] = $rec->getAttribute('telephoneNumber', 0);
            }
        }

        if ($raw_info) {
            if ($adapter->getLogger()) {
                $adapter->getLogger()->logDebug('RESULT: '.print_r($raw_info, 1));
            }

            $identity = new Identity($raw_info['identity'], $raw_info);

            return $identity;
        }

        return;
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_FORM_LOGIN,
            UsersourceInfo::CAPABILITY_FIND_IDENTITY,
        ];
    }
}
