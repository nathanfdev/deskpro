<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * Class Build1517843484.
 */
class Build1517843484 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE ticket_webhook_triggers (webhook_id INT NOT NULL, trigger_id INT NOT NULL, INDEX IDX_9A86BBFC5C9BA60B (webhook_id), UNIQUE INDEX UNIQ_9A86BBFC5FDDDCD6 (trigger_id), PRIMARY KEY(webhook_id, trigger_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhook_triggers ADD CONSTRAINT FK_9A86BBFC5C9BA60B FOREIGN KEY (webhook_id) REFERENCES ticket_webhooks (id)');
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhook_triggers ADD CONSTRAINT FK_9A86BBFC5FDDDCD6 FOREIGN KEY (trigger_id) REFERENCES ticket_triggers (id)');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhooks DROP terms, DROP actions');
    }

    public function run()
    {
    }
}
