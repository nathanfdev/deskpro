<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1572891115 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE label_defs SET label_type = 'chat' WHERE label_type = 'chat_conversations'");
    }
}
