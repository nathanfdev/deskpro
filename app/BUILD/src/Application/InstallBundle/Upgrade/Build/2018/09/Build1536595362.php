<?php

namespace Application\InstallBundle\Upgrade\Build;

// NOTE: I used the BlockingBuildInterface interface because
//       it looks like your schema changes are NOT backwards compatible with the previous version.
//       You should double-check this yourself though. If they are backwards compatible, use OnlineBuildInterface instead.

// Please remove these NOTE comments after you have checked the code.

class Build1536595362 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE app2_app ADD `settings` LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
