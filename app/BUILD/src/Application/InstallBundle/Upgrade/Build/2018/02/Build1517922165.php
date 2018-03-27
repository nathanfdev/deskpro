<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517922165 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE chat_conversations ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE chat_conversations ADD CONSTRAINT FK_5813432E44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_5813432E44F5D008 ON chat_conversations (brand_id)');
    }

    public function run()
    {
    }
}
