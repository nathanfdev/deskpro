<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1538568587 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE import_logs (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, counts LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', date_created DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
