<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * Class Build1542117738.
 */
class Build1542117738 extends AbstractBuild implements OnlineBuildInterface
{
    /**
     * Create tables required for this build.
     */
    public function addNewTables()
    {
        // Create `email_account_log` table
        $this->execDbQuery('default', '
            CREATE TABLE `email_account_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `email_account_id` int(11) NOT NULL,
              `blob_id` int(11) DEFAULT NULL,
              `protocol` varchar(64) DEFAULT NULL,
              `num_emails` int(11) DEFAULT NULL,
              `date_created` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `UNIQ_290FA7A5ED3E8EA5` (`blob_id`),
              KEY `IDX_290FA7A537D8AD65` (`email_account_id`),
              KEY `date_created_idx` (`date_created`),
              CONSTRAINT `FK_290FA7A537D8AD65` FOREIGN KEY (`email_account_id`) REFERENCES `email_accounts` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_290FA7A5ED3E8EA5` FOREIGN KEY (`blob_id`) REFERENCES `blobs` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');
    }

    /**
     * Alter tables required for this build.
     */
    public function runAlters()
    {
        // Alter table `email_sources`, add `email_account_log_id` field and relation
        $this->execDbQuery('default', '
            ALTER TABLE `email_sources` 
            ADD `email_account_log_id` INT(11) DEFAULT NULL,
            ADD KEY `IDX_6F9D0D3D15AC8F3C` (`email_account_log_id`),
            ADD CONSTRAINT `FK_6F9D0D3D15AC8F3C` FOREIGN KEY (`email_account_log_id`) REFERENCES `email_account_logs` (`id`) ON DELETE SET NULL
        ');
    }

    /**
     * Additional routines required for this build.
     */
    public function run()
    {
        // nothing here
    }
}
