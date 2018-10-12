<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1539337127 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', '
            CREATE TABLE IF NOT EXISTS `import_log_blobs` (
                `log_id` int(11) NOT NULL,
                `blob_id` int(11) NOT NULL,
                PRIMARY KEY (`log_id`,`blob_id`),
                UNIQUE KEY `UNIQ_F4D361D4ED3E8EA5` (`blob_id`),
                KEY `IDX_F4D361D4EA675D86` (`log_id`),
                CONSTRAINT `FK_F4D361D4EA675D86` FOREIGN KEY (`log_id`) REFERENCES `import_logs` (`id`),
                CONSTRAINT `FK_F4D361D4ED3E8EA5` FOREIGN KEY (`blob_id`) REFERENCES `blobs` (`id`)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
