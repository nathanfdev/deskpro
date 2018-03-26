<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1509706443 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db = $this->getDbConnection();

        $dateCreated = $db->fetchColumn('SELECT MIN(date_created) FROM snippet_changelog');

        if (!$dateCreated) {
            $dateCreated = date('Y-m-d H:i:s');
        }

        $this->execDbQuery('default', 'UPDATE snippets SET date_created = \''.$dateCreated.'\' WHERE date_created IS NULL');
    }
}
