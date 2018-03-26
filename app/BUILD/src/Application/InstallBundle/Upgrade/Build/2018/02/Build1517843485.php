<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843485 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE scheduled_reports (id BIGINT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, report_id INT DEFAULT NULL, when_setting LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', when_tz VARCHAR(32) NOT NULL, frequency VARCHAR(64) NOT NULL, next_send_date DATETIME DEFAULT NULL, INDEX IDX_95D8F101217BBB47 (person_id), INDEX IDX_95D8F1014BD2A4C0 (report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE scheduled_reports ADD CONSTRAINT FK_95D8F101217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE scheduled_reports ADD CONSTRAINT FK_95D8F1014BD2A4C0 FOREIGN KEY (report_id) REFERENCES report_dashboard_report (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
