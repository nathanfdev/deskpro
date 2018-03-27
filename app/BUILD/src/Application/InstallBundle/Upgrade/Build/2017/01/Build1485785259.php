<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1485785259 extends AbstractBuild
{
    public function run()
    {
        $this->out('Create table zapier_hooks');
        $this->execDbQuery('default', 'CREATE TABLE zapier_hooks (id INT AUTO_INCREMENT NOT NULL, target_url VARCHAR(255) DEFAULT NULL, event VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
    }
}
