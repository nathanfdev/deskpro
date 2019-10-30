<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1571326505 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE article_to_categories ADD display_order INT NOT NULL DEFAULT 0');
    }

    public function run()
    {
    }
}
