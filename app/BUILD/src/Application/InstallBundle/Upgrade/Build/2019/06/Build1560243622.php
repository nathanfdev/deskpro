<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560243622 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db   = $this->getDbConnection('default');
        $apps = $db->fetchAll('SELECT name FROM app_packages WHERE name NOT LIKE \'deskpro_%\'');
        if (count($apps)) {
            $db->insert('settings', [
                'name'  => 'agent.legacy_proxy_whitelist',
                'value' => "http://*\nhttps://*",
            ]);
        }
    }
}
