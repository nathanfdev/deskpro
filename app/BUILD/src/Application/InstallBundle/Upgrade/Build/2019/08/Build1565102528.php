<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565102528 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE ticket_attributes (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, name VARCHAR(250) NOT NULL, value VARCHAR(5000) DEFAULT NULL, date_created DATETIME NOT NULL, dtype VARCHAR(255) NOT NULL, INDEX IDX_A7080C24700047D2 (ticket_id), UNIQUE INDEX attr_name (ticket_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE ticket_attributes ADD CONSTRAINT FK_A7080C24700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('email_sources', 'ADD recipients LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
