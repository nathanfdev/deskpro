<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

class Build1489058013 extends AbstractBuild
{
    public function run()
    {
        $this->out('Inverse agent_data reference');

        $instructions = [];

        $instructions[] = 'ADD agent_data_id INT DEFAULT NULL';
        $instructions[] = 'ADD CONSTRAINT FK_28166A26E5A6C396 FOREIGN KEY (agent_data_id) REFERENCES agent_data (id) ON DELETE SET NULL';
        $instructions[] = 'ADD UNIQUE INDEX UNIQ_28166A26E5A6C396 (agent_data_id)';

        $this->execSlowAlterTableQuiet('people', implode(', ', $instructions));

        $fk = $this->getSchemaHelper()->findForeignKey('agent_data', 'person_id', 'people', 'id');
        if ($fk) {
            $this->out("-- Drop existing FK {$fk->getName()}");
            $this->execDbQuery('default', "ALTER TABLE agent_data DROP FOREIGN KEY {$fk->getName()}");
        }

        $this->execDbQuery('default', 'DROP INDEX UNIQ_684980217BBB47 ON agent_data');
        $this->execDbQuery('default', 'ALTER TABLE agent_data DROP person_id');
    }
}
