<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843483 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE saved_dashboard_widget (id INT AUTO_INCREMENT NOT NULL, dashboard_widget_id INT DEFAULT NULL, saved_report_id INT NOT NULL, title VARCHAR(255) NOT NULL, position VARCHAR(5) NOT NULL, size VARCHAR(5) NOT NULL, type VARCHAR(50) DEFAULT NULL, variables LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', options LONGTEXT DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_7EEC518DB31FDD11 (dashboard_widget_id), INDEX IDX_7EEC518D7E09ED3D (saved_report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE saved_dashboard_report (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, columns INT DEFAULT 24 NOT NULL, variables LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', date_created DATETIME DEFAULT NULL, authcode VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->execDbQuery('default', 'ALTER TABLE saved_dashboard_widget ADD CONSTRAINT FK_7EEC518DB31FDD11 FOREIGN KEY (dashboard_widget_id) REFERENCES report_widget (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE saved_dashboard_widget ADD CONSTRAINT FK_7EEC518D7E09ED3D FOREIGN KEY (saved_report_id) REFERENCES saved_dashboard_report (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
