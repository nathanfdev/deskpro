<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1611142729 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{

    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE guest_emails (id BIGINT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, ipAddress VARCHAR(180) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_564EC2B8E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
