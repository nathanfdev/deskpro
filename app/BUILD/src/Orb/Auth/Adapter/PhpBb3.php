<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

class PhpBb3 extends DbTable
{
    const OPT_TABLE_PREFIX      = 'table_prefix';
    const OPT_CHECK_SERVICE_URL = 'check_service_url';
    const OPT_CHECK_SERVICE_KEY = 'check_service_key';

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
        $data = [
            'key'      => $this->options->get(self::OPT_CHECK_SERVICE_KEY) ?: 'dp_login_check',
            'username' => $this->set_username,
            'password' => $this->set_password,
        ];

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data, null, '&'),
            ],
        ]);

        $result = @file_get_contents($this->options->get(self::OPT_CHECK_SERVICE_URL), null, $context);

        if (!$result || strpos($result, 'LOGIN_SUCCESS') === false) {
            return false;
        }

        return true;
    }
}
