<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

class Build1454263358 extends AbstractBuild
{
    public function run()
    {
        $this->out('Create job to update who is online');
        $this->execMutateSql("INSERT INTO `worker_jobs` (`id`, `worker_group`, `title`, `description`, `job_class`, `data`, `run_interval`, `last_run_date`) VALUES ('update_agents_online', 'update_agents_online', 'Update who\'s online', 'Gathers info about who is online from agents and emits action alert', 'Application\\\\DeskPRO\\\\WorkerProcess\\\\Job\\\\UpdateAgentsOnline', '0x613A303A7B7D', 120, '2016-01-31 20:01:48');");
    }
}
