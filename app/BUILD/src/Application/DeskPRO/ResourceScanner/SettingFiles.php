<?php

/**
 * DeskPRO.
 *
 * @category Controller
 */

namespace Application\DeskPRO\ResourceScanner;

class SettingFiles
{
    public function getAllSettings()
    {
        $settings = require DP_ROOT.'/sys/config/settings.php';

        return $settings;
    }
}
