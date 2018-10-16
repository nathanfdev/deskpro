<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1539350027 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQueryQuiet('default', 'CREATE TABLE IF NOT EXISTS import_logs (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, counts LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', date_created DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQueryQuiet('default', '
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
