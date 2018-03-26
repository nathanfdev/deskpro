<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517829103 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD department_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EAAE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_80C86EAAE80F5DF ON voice_queues (department_id)');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD voice_queue_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD CONSTRAINT FK_6679AE4C2E24EDAB FOREIGN KEY (voice_queue_id) REFERENCES voice_queues (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_6679AE4C2E24EDAB ON voice_phone_calls (voice_queue_id)');
    }

    public function run()
    {
    }
}
