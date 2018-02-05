<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
