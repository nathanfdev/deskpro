<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1503398257 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE snippet_translations ADD type VARCHAR(255) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE snippets ADD is_split TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function run()
    {
    }
}
