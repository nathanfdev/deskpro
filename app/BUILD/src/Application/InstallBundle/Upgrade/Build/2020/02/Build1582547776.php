<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1582547776 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        if (!$this->getSchemaHelper()->tableHasColumn('email_sources', 'client_ip')) {
            $this->execSlowAlterTable(
                'email_sources',
                "ADD client_ip VARCHAR(130) DEFAULT NULL, ADD client_host VARCHAR(255) DEFAULT NULL"
            );
        }
    }

    public function run()
    {
    }
}
