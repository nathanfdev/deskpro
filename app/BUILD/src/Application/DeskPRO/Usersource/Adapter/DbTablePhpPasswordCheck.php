<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class DbTablePhpPasswordCheck extends AbstractAdapter
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'name'            => isset($info['name']) ? $info['name'] : '',
            'first_name'      => isset($info['first_name']) ? $info['first_name'] : '',
            'last_name'       => isset($info['last_name']) ? $info['last_name'] : '',
            'email'           => isset($info['email_address']) ? $info['email_address'] : '',
            'email_confirmed' => true,
        ];
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDb()
    {
        if ($this->db) {
            return $this->db;
        }

        if ($this->usersource->getOption('connection_options')) {
            $options = $this->usersource->getOption('connection_options');
            if (is_string($options)) {
                $options = json_decode($options);
            }

            $this->db = \Doctrine\DBAL\DriverManager::getConnection($options);
        } else {
            $pdo = new \PDO(
                $this->usersource->getOption('db_dsn'),
                $this->usersource->getOption('db_username'),
                $this->usersource->getOption('db_password')
            );

            $this->db = \Doctrine\DBAL\DriverManager::getConnection(['pdo' => $pdo]);
        }

        return $this->db;
    }

    /**
     * @return callable
     */
    public function getDbAsCallback()
    {
        $me = $this;

        return function () use ($me) {
            return $me->getDb();
        };
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
        /** @var $adapter \Orb\Auth\Adapter\DbTable.php */
        $adapter = $this->getAuthAdapter();

        $userinfo = null;

        try {
            if (\Orb\Validator\StringEmail::isValueValid($id_input)) {
                $userinfo = $adapter->getUserInfoForEmail($id_input);
            }
            if (!$userinfo) {
                $userinfo = $adapter->getUserInfoForUsername($id_input);
            }
            if (!$userinfo) {
                $userinfo = $adapter->getUserInfoForId($id_input);
            }
        } catch (\Exception $e) {
            if ($adapter->getLogger()) {
                $adapter->getLogger()->logDebug("findIdentityByInput Exception: {$e->getCode()} {$e->getMessage()}");
            }
            throw $e;
        }

        if (!$userinfo) {
            return;
        }

        $identify = $adapter->getIdentityFromUserInfo($userinfo);

        return $identify;
    }

    /**
     * @return \Orb\Auth\Adapter\DbTablePhpPasswordCheck
     */
    protected function _createAuthAdapterObject()
    {
        return new \Orb\Auth\Adapter\DbTablePhpPasswordCheck($this->getDbAsCallback(), $this->usersource->options);
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
