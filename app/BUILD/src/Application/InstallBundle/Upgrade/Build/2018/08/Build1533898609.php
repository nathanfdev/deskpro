<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1533898609 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'UPDATE languages SET locale = REPLACE(locale, \'-\', \'_\')');
    }
}
