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

class Build1479817824 extends AbstractBuild
{
    public function run()
    {
        $this->out('Populate deparment triggers with api by_user_mode');
        $connection = $this->getDbConnection('default');
        $sql        = <<<'SQL'
        SELECT * FROM `ticket_triggers` 
        WHERE `department_id` IS NOT NULL
        AND `by_user_mode` NOT LIKE "%api%"
SQL;

        $triggersWithoutApiMode = $connection->fetchAll($sql);
        $statement              = $connection->prepare('UPDATE `ticket_triggers` SET `by_user_mode` = :modes WHERE `id` = :id');
        foreach ($triggersWithoutApiMode as $trigger) {
            $modes = $trigger['by_user_mode'];
            $modes = explode(',', $modes);
            array_unshift($modes, 'api');
            array_map(function (&$item) {
                $item = trim($item);
            }, $modes);
            $statement->execute(['id' => $trigger['id'], 'modes' => implode(',', $modes)]);
        }
    }
}
