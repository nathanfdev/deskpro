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

use DeskPRO\Bundle\UpdateBundle\BuildTasks\BuildFactory;
use DeskPRO\Bundle\UpdateBundle\BuildTasks\BuildRunner;

class Build1503398250 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        /*
         * These upgrades might've been skipped depending on when a database was upgraded.
         *
         * Imagine build 10, 20, 30. User is on build 10, we release build 20 and 30. User upgrades to 30.
         * After 30, we release build 25. Since user is already "ahead', 25 never gets run.
         *
         * This happened (out of order builds) in a release due a merge and builds becoming out of order.
         */

        // If the snippets table doesnt exist, this is an indicator
        if ($this->getSchemaHelper()->getSchemaManager()->tablesExist('snippets')) {
            // exists, so nothing to do
            return;
        }

        $versions = ['1498832255', '1498832256', '1498832257', '1498832258', '1499089832'];

        $buildStatus    = $this->container->get('dp.build_tasks.build_status');
        $manifestReader = $this->container->get('dp.build_tasks.manifest_reader');
        $buildFactory   = new BuildFactory($manifestReader, $this->container, $this->logger);
        $buildRunner    = new BuildRunner($buildFactory, $manifestReader, $this->logger);

        foreach ($versions as $v) {
            if (!$buildStatus->hasBuildRun($v)) {
                $this->out("Running outstanding build: $v");
                $buildRunner->runBuild($v);
            }
        }
    }
}
