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
