<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1546868707 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQueryQuiet('default', 'ALTER TABLE languages ADD title_local VARCHAR(255) DEFAULT \'\', ADD plural_categories VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ADD plural_formula VARCHAR(255) DEFAULT \'n != 1\' NOT NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE phrases ADD is_managed TINYINT(1) DEFAULT \'0\'');
    }

    public function run()
    {
    }
}
