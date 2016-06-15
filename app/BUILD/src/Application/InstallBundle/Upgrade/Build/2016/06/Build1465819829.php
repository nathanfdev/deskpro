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

class Build1465819829 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade ban ip table');
        $db        = $this->getDbConnection('default');
        $bans      = $db->fetchAll('SELECT * from `ban_ips`');
        $createSql = <<<SQL
            CREATE TABLE ban_ips (
              id INT AUTO_INCREMENT NOT NULL, 
              banned_ip VARCHAR(100) NOT NULL, 
              is_range TINYINT(1) NOT NULL, 
              INDEX is_range_idx (is_range),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
SQL;
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS `ban_ips`');
        $this->execDbQuery('default', $createSql);
        $newBans = [];
        foreach ($bans as $ban) {
            $newBans[] = $this->convertBan($ban['banned_ip']);
        }

        if ($newBans) {
            $db->batchInsert('ban_ips', $newBans);
        }
    }

    private function convertBan($ip)
    {
        $newBan = [
            'is_range' => false,
        ];
        // we already have a mask
        if (strpos($ip, '/') !== false) {
            list($ip, $blocks)  = explode('/', $ip, 2);
            $newBan['is_range'] = true;
            $defaultBlocks      = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 128 : 32;
        } else {
            $blocks = $defaultBlocks = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 128 : 32;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // checking 192.168.*.* format
            $ip    = str_replace('.*', '', $ip);
            $count = 0;
            $ip    = explode('.', $ip);
            for ($i = count($ip); $i < 4; ++$i) {
                $ip[] = 0;
                ++$count;
            }
            $ip = implode('.', $ip);

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $count > 0) {
                $newBan['is_range'] = true;
                $blocks -= ($count * 8);
            }
        }
        $humanReadable       = $ip.(($blocks && $blocks != $defaultBlocks) ? "/$blocks" : '');
        $newBan['banned_ip'] = $humanReadable;

        return $newBan;
    }
}
