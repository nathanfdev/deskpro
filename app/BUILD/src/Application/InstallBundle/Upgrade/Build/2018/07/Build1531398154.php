<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1531398154 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE report_dashboard_shareable_links (id INT AUTO_INCREMENT NOT NULL, dashboard_id INT NOT NULL, default_report_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, auth_code VARCHAR(255) NOT NULL, who_can_use VARCHAR(50) NOT NULL, ip_whitelist LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_102B10F7B9D04D2B (dashboard_id), INDEX IDX_102B10F7DAB08C67 (default_report_id), UNIQUE INDEX auth_code (auth_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE report_dashboard_shareable_short_url (id INT AUTO_INCREMENT NOT NULL, shareable_link_id INT NOT NULL, auth_code VARCHAR(50) NOT NULL, date_created DATETIME NOT NULL, date_expire DATETIME NOT NULL, INDEX IDX_5A1035FE8D25E5C2 (shareable_link_id), UNIQUE INDEX auth_code (auth_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_shareable_links ADD CONSTRAINT FK_102B10F7B9D04D2B FOREIGN KEY (dashboard_id) REFERENCES report_dashboard (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_shareable_links ADD CONSTRAINT FK_102B10F7DAB08C67 FOREIGN KEY (default_report_id) REFERENCES report_dashboard_report (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_shareable_short_url ADD CONSTRAINT FK_5A1035FE8D25E5C2 FOREIGN KEY (shareable_link_id) REFERENCES report_dashboard_shareable_links (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
