<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1538568587 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE import_logs (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, counts LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', date_created DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE log_blobs (log_id INT NOT NULL, blob_id INT NOT NULL, INDEX IDX_A912D568EA675D86 (log_id), UNIQUE INDEX UNIQ_A912D568ED3E8EA5 (blob_id), PRIMARY KEY(log_id, blob_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE log_blobs ADD CONSTRAINT FK_A912D568EA675D86 FOREIGN KEY (log_id) REFERENCES import_logs (id)');
        $this->execDbQuery('default', 'ALTER TABLE log_blobs ADD CONSTRAINT FK_A912D568ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id)');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
