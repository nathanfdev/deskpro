<?php
namespace Application\InstallBundle\Upgrade\Build;

use Orb\Util\Strings;

class Build1592308935 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        if (!defined('DPC_IS_CLOUD')) {
            return;
        }
        $this->out('Rotate app_secret');

        // backup the old one just in case its needed
        $this->execDbQuery('default', "
            INSERT INTO settings (name, value)
                SELECT CONCAT('core.app_secret_old_', UNIX_TIMESTAMP()), value
                FROM settings
                WHERE name = 'core.app_secret'
        ");

        // generate a new one
        $this->saveSetting('core.app_secret', Strings::random(75));
    }
}
