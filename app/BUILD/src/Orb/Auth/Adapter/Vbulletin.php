<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

class Vbulletin extends DbTable
{
    const OPT_TABLE_PREFIX = 'table_prefix';

    protected function initOptions()
    {
        parent::initOptions();

        $this->options[self::OPT_TABLE]          = $this->options->get(self::OPT_TABLE_PREFIX, '').'user';
        $this->options[self::OPT_FIELD_ID]       = 'userid';
        $this->options[self::OPT_FIELD_USERNAME] = 'username';
        $this->options[self::OPT_FIELD_PASSWORD] = 'password';
        $this->options[self::OPT_FIELD_EMAIL]    = 'email';
    }

    protected function isValidPassword(array $userinfo, $password_input)
    {
        $hashed = md5(md5($password_input).$userinfo['salt']);

        if ($userinfo['password'] == $hashed) {
            return true;
        }

        return false;
    }
}
