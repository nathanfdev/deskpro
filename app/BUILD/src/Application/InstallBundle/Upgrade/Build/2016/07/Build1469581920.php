<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1469581920 extends AbstractBuild
{
    public function run()
    {
        $this->out('Recreate system alerts tables');
        $this->execDbQuery('system', 'SET FOREIGN_KEY_CHECKS = 0');
        $this->execDbQuery('system', 'DROP TABLE IF EXISTS `system_alerts_incident_events`');
        $this->execDbQuery('system', 'DROP TABLE IF EXISTS `system_alerts_events`');
        $this->execDbQuery('system', 'DROP TABLE IF EXISTS `system_alerts_incidents`');
        $this->execDbQuery('system', 'SET FOREIGN_KEY_CHECKS = 1');
        $this->execDbQuery('system', "CREATE TABLE system_alerts_events (id INT AUTO_INCREMENT NOT NULL, subject_unique_id VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, processed TINYINT(1) NOT NULL, expiration_strategy VARCHAR(255) NOT NULL, type VARCHAR(30) NOT NULL, exception_class VARCHAR(255) DEFAULT NULL, exception_code INT UNSIGNED DEFAULT NULL, message VARCHAR(255) DEFAULT NULL, trace LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', file VARCHAR(255) DEFAULT NULL, line INT UNSIGNED DEFAULT NULL, email_account_id INT UNSIGNED DEFAULT NULL, email_account_address VARCHAR(255) DEFAULT NULL, error_type INT UNSIGNED DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execDbQuery('system', "CREATE TABLE system_alerts_incidents (id INT AUTO_INCREMENT NOT NULL, first_failure_event_id INT DEFAULT NULL, last_failure_event_id INT DEFAULT NULL, subject_unique_id VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, failure_events_count INT NOT NULL, success_events_count INT NOT NULL, event_dates LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', raised TINYINT(1) NOT NULL, dismissed TINYINT(1) NOT NULL, type VARCHAR(30) NOT NULL, resolved TINYINT(1) DEFAULT NULL, INDEX IDX_12A50A6662493C9F (first_failure_event_id), INDEX IDX_12A50A667908C2BA (last_failure_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execDbQuery('system', 'CREATE TABLE system_alerts_incident_events (incident_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_AA906E6D59E53FB9 (incident_id), INDEX IDX_AA906E6D71F7E88B (event_id), PRIMARY KEY(incident_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('system', 'ALTER TABLE system_alerts_incidents ADD CONSTRAINT FK_12A50A6662493C9F FOREIGN KEY (first_failure_event_id) REFERENCES system_alerts_events (id)');
        $this->execDbQuery('system', 'ALTER TABLE system_alerts_incidents ADD CONSTRAINT FK_12A50A667908C2BA FOREIGN KEY (last_failure_event_id) REFERENCES system_alerts_events (id)');
        $this->execDbQuery('system', 'ALTER TABLE system_alerts_incident_events ADD CONSTRAINT FK_AA906E6D59E53FB9 FOREIGN KEY (incident_id) REFERENCES system_alerts_incidents (id) ON DELETE CASCADE');
        $this->execDbQuery('system', 'ALTER TABLE system_alerts_incident_events ADD CONSTRAINT FK_AA906E6D71F7E88B FOREIGN KEY (event_id) REFERENCES system_alerts_events (id) ON DELETE CASCADE');
    }
}
