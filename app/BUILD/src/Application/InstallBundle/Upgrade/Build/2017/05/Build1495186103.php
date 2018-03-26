<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1495186103 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD voicemail_timeout INT NOT NULL');
    }

    public function run()
    {
    }
}
