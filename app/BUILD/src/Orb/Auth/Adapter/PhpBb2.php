<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

class PhpBb2 extends DbTable
{
    const OPT_TABLE_PREFIX = 'table_prefix';

    protected function initOptions()
    {
        parent::initOptions();

        $this->options[self::OPT_TABLE]          = $this->options->get(self::OPT_TABLE_PREFIX, '').'users';
        $this->options[self::OPT_FIELD_ID]       = 'user_id';
        $this->options[self::OPT_FIELD_USERNAME] = 'username';
        $this->options[self::OPT_FIELD_PASSWORD] = 'user_password';
        $this->options[self::OPT_FIELD_EMAIL]    = 'user_email';
    }

    protected function isValidPassword(array $userinfo, $password_input)
    {
        $hashed = md5($password_input);

        if ($userinfo['user_password'] == $hashed) {
            return true;
        }

        return false;
    }
}
