<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1582547780 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQueryQuiet('default', 'ALTER TABLE voice_queues ADD voicemail_disabled TINYINT(1) NOT NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE voice_queues ADD voicemail_disabled_asset INT DEFAULT NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA58D577E0 FOREIGN KEY (voicemail_disabled_asset) REFERENCES voice_assets (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'CREATE UNIQUE INDEX UNIQ_80C86EA58D577E0 ON voice_queues (voicemail_disabled_asset)');
    }

    public function run()
    {
    }
}
