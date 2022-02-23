<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1643991694 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('default', "
            ALTER TABLE `blobs`
            ADD COLUMN `source_ref` varchar(255) NULL,
            DROP INDEX `date_created_idx`,
            ADD INDEX `date_created_idx` (`date_created`,`is_temp`,`source_ref`)
        ");
    }

    public function run()
    {
    }
}
