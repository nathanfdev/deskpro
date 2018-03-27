<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO;

/**
 * Helper that just checks for the current installs capabilities.
 */
class InstanceAbility
{
    public function canUseSsl()
    {
        static $has_ssl;

        if ($has_ssl === null) {
            $has_ssl = extension_loaded('openssl');
        }

        return $has_ssl;
    }

    public function canUseFacebookAuth()
    {
        return $this->canUseSsl();
    }

    public function canUseTwitterAuth()
    {
        return $this->canUseSsl();
    }

    public function canUseGoogleAuth()
    {
        return $this->canUseSsl();
    }

    public function canUseSecurePop3()
    {
        return $this->canUseSsl();
    }

    public function canUseSecureSmtp()
    {
        return $this->canUseSsl();
    }

    public function canUseGoogleApps()
    {
        return $this->canUseSsl();
    }

    public function isWindows()
    {
        if (strpos(strtoupper(PHP_OS), 'WIN') === 0) {
            return true;
        }

        return false;
    }

    public function isIis()
    {
        if ($this->isWindows() && strpos(strtolower(@$_SERVER['SERVER_SOFTWARE'] ?: ''), 'iis') !== false) {
            return true;
        }

        return false;
    }

    public function __call($method, array $args = [])
    {
        return false;
    }
}
