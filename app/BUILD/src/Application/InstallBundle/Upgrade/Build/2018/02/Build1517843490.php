<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843490 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhook_triggers DROP FOREIGN KEY FK_9A86BBFC5C9BA60B');
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhook_triggers ADD CONSTRAINT FK_9A86BBFC5C9BA60B FOREIGN KEY (webhook_id) REFERENCES ticket_webhooks (id) ON DELETE CASCADE');

        $this->execDbQuery('default', 'ALTER TABLE ticket_webhook_triggers DROP FOREIGN KEY FK_9A86BBFC5FDDDCD6');
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhook_triggers ADD CONSTRAINT FK_9A86BBFC5FDDDCD6 FOREIGN KEY (trigger_id) REFERENCES ticket_triggers (id) ON DELETE CASCADE');
    }

    public function run()
    {
    }
}
