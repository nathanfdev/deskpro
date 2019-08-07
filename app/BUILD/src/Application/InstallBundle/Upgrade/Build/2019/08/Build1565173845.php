<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565173845 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_logs ADD target_agent_id INT DEFAULT NULL, ADD target_queue_id INT DEFAULT NULL, ADD target_auto_attendant_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_logs ADD CONSTRAINT FK_ACB69564EEBFA162 FOREIGN KEY (target_agent_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_logs ADD CONSTRAINT FK_ACB695649DD08BC7 FOREIGN KEY (target_queue_id) REFERENCES voice_queues (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_logs ADD CONSTRAINT FK_ACB69564D6E198CF FOREIGN KEY (target_auto_attendant_id) REFERENCES voice_auto_attendants (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_ACB69564EEBFA162 ON voice_phone_call_logs (target_agent_id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_ACB695649DD08BC7 ON voice_phone_call_logs (target_queue_id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_ACB69564D6E198CF ON voice_phone_call_logs (target_auto_attendant_id)');
    }

    public function run()
    {
    }
}
