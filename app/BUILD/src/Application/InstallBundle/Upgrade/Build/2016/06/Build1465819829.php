<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1465819829 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade ban ip table');
        $db        = $this->getDbConnection('default');
        $bans      = $db->fetchAll('SELECT * from `ban_ips`');
        $createSql = <<<'SQL'
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
