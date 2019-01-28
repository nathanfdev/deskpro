<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1548685843 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE api_keys SET flags = CONCAT(flags, ',api_v1,api_v2') WHERE flags IS NOT NULL AND flags != ''");
        $this->execDbQuery('default', "UPDATE api_keys SET flags = 'api_v1,api_v2' WHERE flags IS NULL OR flags = ''");
    }
}
