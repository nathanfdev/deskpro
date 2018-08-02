<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1533147336 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $usersources = $this->getDbConnection('default')->fetchAll('SELECT id, source_type FROM `usersources`');
        $idsToDelete = [];
        foreach ($usersources as $usersource) {
            if (!class_exists($usersource['source_type'])) {
                $idsToDelete[] = $usersource['id'];
            }
        }
        if ($idsToDelete) {
            $this->getDbConnection('default')->deleteIn('usersources', $idsToDelete);
        }
    }
}
