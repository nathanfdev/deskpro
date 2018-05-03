<?php

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Orb\Util\DpStrings;
use Orb\Util\Strings;

class SettingsData extends AbstractDefaultData
{
    public function runInstall()
    {
        $this->getDb()->replace('settings', [
            'name'  => 'core.app_secret',
            'value' => DpStrings::random(75, Strings::CHARS_KEY),
        ]);
    }

    public function runReset()
    {
        $this->runInstall();
    }

    public function runSync()
    {
        $exist = $this->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.app_secret'");
        if (!$exist) {
            $this->runInstall();
        }
    }
}
