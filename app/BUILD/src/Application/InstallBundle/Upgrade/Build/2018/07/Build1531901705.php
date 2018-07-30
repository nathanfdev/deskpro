<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1531901705 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        // fix local usersources if reg is enabled but somehow the usersources is disabled
        $localUsersources = $this->getDbConnection('default')->fetchAll('SELECT id, is_enabled, options FROM `usersources` WHERE app_id IS NULL');

        foreach ($localUsersources as $usersource) {
            $decodedOptions = json_decode($usersource['options'], true);
            if (isset($decodedOptions['reg_enabled']) && $decodedOptions['reg_enabled'] && !$usersource['is_enabled']) {
                $statement = $this->getDbConnection('default')->prepare('UPDATE `usersources` SET is_enabled = 1 WHERE `id` = :id');
                $statement->execute([
                    'id' => $usersource['id'],
                ]);
            }
        }
    }
}
