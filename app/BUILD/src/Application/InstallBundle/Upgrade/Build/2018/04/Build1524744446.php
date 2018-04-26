<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1524744446 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $regEnabled       = (bool) $this->readSetting('core.reg_enabled');
        $localUsersources = $this->getDbConnection('default')->fetchAll('SELECT id, options FROM `usersources` WHERE app_id IS NULL');

        foreach ($localUsersources as $usersource) {
            $decodedOptions                = json_decode($usersource['options'], true);
            $decodedOptions['reg_enabled'] = $regEnabled;
            $encodedOptions                = json_encode($decodedOptions);

            $statement = $this->getDbConnection('default')->prepare('UPDATE `usersources` SET options = :options WHERE `id` = :id');
            $statement->execute([
                'id'      => $usersource['id'],
                'options' => $encodedOptions,
            ]);
        }
    }
}
