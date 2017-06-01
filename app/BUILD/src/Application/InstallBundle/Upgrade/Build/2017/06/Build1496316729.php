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

class Build1496316729 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $badIndex = $this->getSchemaHelper()->findIndex('app2_app_state', ['app_instance_id', 'name']);
        if ($badIndex) {
            $this->out('Dropping invalid index on app2_app_state');
            $this->execDbQuery('default', "DROP INDEX `{$badIndex->getName()}` ON app2_app_state");
        }

        $goodIndex = $this->getSchemaHelper()->findIndex('app2_app_state', ['app_instance_id', 'name', 'owner_id']);
        if (!$goodIndex) {
            $this->out('Creating correct index on app2_app_state');
            $this->execDbQuery('default', 'CREATE UNIQUE INDEX state_unique ON app2_app_state (app_instance_id, name, owner_id)');
        }
    }

    public function run()
    {
    }
}
