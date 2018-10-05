<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1538748565 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE languages ADD title_local VARCHAR(255) DEFAULT \'\', ADD plural_categories VARCHAR(255) DEFAULT \'one,other\' NOT NULL COMMENT \'(DC2Type:simple_array)\', ADD plural_formula VARCHAR(255) DEFAULT \'n != 1\' NOT NULL');
    }

    public function run()
    {
    }
}
