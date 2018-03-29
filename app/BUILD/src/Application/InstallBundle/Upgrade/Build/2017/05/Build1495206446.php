<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1495206446 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'DROP TABLE notify_action_alerts;');
        $this->execDbQuery('default', 'DROP TABLE notify_notifications;');
        $this->execDbQuery('default', 'CREATE TABLE notify_action_alerts (id BIGINT AUTO_INCREMENT NOT NULL, target_id INT NOT NULL, uuid VARCHAR(80) NOT NULL, date_created DATETIME NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', type VARCHAR(100) NOT NULL, INDEX target_id (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE notify_notifications (id BIGINT AUTO_INCREMENT NOT NULL, target_id INT NOT NULL, uuid VARCHAR(80) NOT NULL, date_created DATETIME NOT NULL, is_dismissed TINYINT(1) DEFAULT \'0\' NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', type VARCHAR(100) NOT NULL, INDEX target_id (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
