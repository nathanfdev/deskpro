<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1558017674 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_queues DROP FOREIGN KEY FK_80C86EAAE80F5DF');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EAAE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_80C86EA44F5D008 ON voice_queues (brand_id)');
    }

    public function run()
    {
    }
}
