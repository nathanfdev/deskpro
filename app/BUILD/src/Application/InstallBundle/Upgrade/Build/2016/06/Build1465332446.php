<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

class Build1465332446 extends AbstractBuild
{
    public function run()
    {
        $this->out('Update rate_limit_log ip');
        // I really don't know why the set `ip` = INET_NTOA(CAST(`id` AS INTEGER)) wont work properply
        $connection = $this->getDbConnection('default');
        $logs       = $connection->fetchAll('SELECT * FROM `rate_limit_log`');
        $this->execDbQuery('default', 'ALTER TABLE `rate_limit_log` CHANGE `ip` `ip` VARCHAR(255) NOT NULL');
        $sql = <<<SQL
        UPDATE `rate_limit_log` SET `ip` = :ip WHERE `id` = :id
SQL;
        $statement = $connection->prepare($sql);
        foreach ($logs as $log) {
            $statement->execute(['ip' => long2ip($log['ip']), 'id' => $log['id']]);
        }
    }
}
