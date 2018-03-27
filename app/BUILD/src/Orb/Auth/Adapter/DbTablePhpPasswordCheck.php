<?php

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

class DbTablePhpPasswordCheck extends DbTable
{
    const OPT_PASSWORD_PHP = 'password_php';

    protected function isValidPassword(array $userinfo, $password_input)
    {
        $field_id       = $this->options->get(self::OPT_FIELD_ID);
        $field_username = $this->options->get(self::OPT_FIELD_USERNAME);
        $field_email    = $this->options->get(self::OPT_FIELD_EMAIL);
        $field_password = $this->options->get(self::OPT_FIELD_PASSWORD);

        $user_id       = $field_id && isset($userinfo[$field_id]) ? $userinfo[$field_id] : null;
        $user_username = $field_username && isset($userinfo[$field_username]) ? $userinfo[$field_username] : null;
        $user_email    = $field_email && isset($userinfo[$field_email]) ? $userinfo[$field_email] : null;

        $userinfo_password = $field_password && isset($userinfo[$field_password]) ? $userinfo[$field_password] : null;
        $password_recorded = $userinfo_password;

        if ($this->logger) {
            $this->logger->logDebug("Found user record: $user_id $user_username $user_email");
        }

        if (!$this->getDb()) {
            return false;
        }
        $db = $this->getDb();

        $is_valid = false;
        // may require $password_recorder and $db?
        eval($this->options->get('password_php'));
        $is_valid = (bool) $is_valid;

        if ($is_valid) {
            if ($this->logger) {
                $this->logger->logDebug('Passed password check');
            }
        } else {
            if ($this->logger) {
                $this->logger->logDebug('Failed password check');
            }
        }

        return $is_valid;
    }
}
