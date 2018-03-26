<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

class OsCommerce extends DbTable
{
    protected function initOptions()
    {
        parent::initOptions();

        $this->options[self::OPT_FIELD_ID]       = 'customers_id';
        $this->options[self::OPT_FIELD_USERNAME] = null;
        $this->options[self::OPT_FIELD_PASSWORD] = 'customers_password';
        $this->options[self::OPT_FIELD_EMAIL]    = 'customers_email_address';
    }

    protected function isValidPassword(array $userinfo, $password_input)
    {
        list($pass_hash, $pass_salt) = explode(':', $userinfo['customers_password']);

        $hashed_input = md5($pass_salt.$password_input);

        if ($hashed_input == $pass_hash) {
            return true;
        }

        return false;
    }
}
