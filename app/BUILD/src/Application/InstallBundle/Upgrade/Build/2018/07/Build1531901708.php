<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1531901708 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        // Applies to cloud only
        if (!defined('DPC_IS_CLOUD')) {
            return;
        }

        // Make sure URLs are all https
        foreach (['settings', 'settings_brand'] as $table) {
            // delete existing setting (default is to auto-correct)
            $i = $this->execDbQuery('default', "DELETE FROM $table WHERE name = 'core.deskpro_url_autocorrect'");
            $this->out("[$table] Unset core.deskpro_url_autocorrect ($i)");

            // ensure https
            $i = $this->execDbQuery('default', "
                UPDATE $table
                SET value = REPLACE(value, 'http:', 'https:')
                WHERE
                    name = 'core.deskpro_url'
                    AND value LIKE 'http:%'
            ");
            $this->out("[$table] Update core.deskpro_url ($i)");
        }
    }
}
