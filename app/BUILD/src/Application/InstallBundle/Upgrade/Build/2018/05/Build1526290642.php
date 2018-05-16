<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1526290642 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE usersource_to_brand (usersource_id INT NOT NULL, brand_id INT NOT NULL, INDEX IDX_E3B27C6C5B71BD01 (usersource_id), INDEX IDX_E3B27C6C44F5D008 (brand_id), PRIMARY KEY(usersource_id, brand_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE usersource_to_brand ADD CONSTRAINT FK_E3B27C6C5B71BD01 FOREIGN KEY (usersource_id) REFERENCES usersources (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE usersource_to_brand ADD CONSTRAINT FK_E3B27C6C44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE usersources ADD is_all_brands TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function run()
    {
    }
}
