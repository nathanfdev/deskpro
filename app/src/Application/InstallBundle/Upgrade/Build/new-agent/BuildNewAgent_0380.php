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

class BuildNewAgent_0380 extends AbstractBuild
{
    public function run()
    {
        $this->out('add feedback subscriptions worker job');

        $this->execMutateSql(
            "
            INSERT INTO `worker_jobs` (`id`, `worker_group`, `title`, `description`, `job_class`, `data`, `run_interval`, `last_run_date`, `last_start_date`)
            VALUES ('feedback_subscriptions', 'feedback_subscriptions', 'Feedback Subscriptions', 'Sends notifications to users who are subscribed to feedback items', 'Application\\\\DeskPRO\\\\WorkerProcess\\\\Job\\\\FeedbackSubscriptions', X'613A303A7B7D', '7200', NULL, NULL)
        "
        );

        // NOTE TO FUTURE: we had added news/downloads worker jobs in the past (they are in Build1421095053)
    }
}

//[[build:1456790438]]

