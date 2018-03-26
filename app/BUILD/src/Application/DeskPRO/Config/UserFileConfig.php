<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Config;

/**
 * Loads config from/sys/config/config.xxx.php and merges it with /config.xxx.php if it exists.
 */
class UserFileConfig extends \Orb\Util\OptionsArray
{
    public function __construct($name)
    {
        $array = [];

        $sys_file = DP_ROOT.'/sys/config/config.'.$name.'.php';

        if (file_exists($sys_file)) {
            $array = require $sys_file;
        }

        parent::__construct($array);
    }
}
