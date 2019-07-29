<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1562594876 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD answer_timeout INT NOT NULL');
    }

    public function run()
    {
    }
}
