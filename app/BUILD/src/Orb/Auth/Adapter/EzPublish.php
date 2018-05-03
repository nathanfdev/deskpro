<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

class EzPublish extends DbTable
{
    protected function initOptions()
    {
        parent::initOptions();

        $this->options[self::OPT_TABLE]          = 'ezuser';
        $this->options[self::OPT_FIELD_ID]       = 'contentobject_id';
        $this->options[self::OPT_FIELD_USERNAME] = 'login';
        $this->options[self::OPT_FIELD_PASSWORD] = 'password_hash';
        $this->options[self::OPT_FIELD_EMAIL]    = 'email';
    }

    protected function isValidPassword(array $userinfo, $password_input)
    {
        switch ($userinfo['password_hash_type']) {

            // EZ_USER_PASSWORD_HASH_MD5_PASSWORD
            case 2:
                $password_check = md5("{$userinfo['login']}\n{$password_input}");
                break;

            // EZ_USER_PASSWORD_HASH_MYSQL
            case 4:
                $password_check = $this->getDb()->executeQuery('SELECT PASSWORD(?)', [$password_input])->fetchColumn();
                break;

            // EZ_USER_PASSWORD_HASH_PLAINTEXT
            case 5:
                $password_check = $password_input;
                break;

            // EZ_USER_PASSWORD_HASH_CRYPT
            case 6:
                $password_check = crypt($password_input);
                break;

            // EZ_USER_PASSWORD_HASH_MD5_PASSWORD
            case 1:
            default:
                $password_check = md5($password_input);
        }

        if ($userinfo[$this->options[self::OPT_FIELD_PASSWORD]] == $password_check) {
            return true;
        }

        return false;
    }
}
