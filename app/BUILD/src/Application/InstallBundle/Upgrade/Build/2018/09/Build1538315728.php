<?php
namespace Application\InstallBundle\Upgrade\Build;

/**
 * Class Build1538315728
 * @package Application\InstallBundle\Upgrade\Build
 */
class Build1538315728 extends AbstractBuild implements OnlineBuildInterface
{
    /**
     * Create tables required for this build
     */
    public function addNewTables()
    {
        // Create `email_account_log` table
        $this->execDbQuery('default', "
            CREATE TABLE `email_account_logs`
            (
              `id` INT(11) NOT NULL AUTO_INCREMENT, 
              `email_account_id` INT(11) NOT NULL, 
              `protocol` VARCHAR(64) COLLATE utf8_unicode_ci DEFAULT NULL,
              `blob_id` INT(11) DEFAULT NULL,
              `num_emails` INT(11) DEFAULT NULL,
              `date_created` DATETIME DEFAULT NULL,
              PRIMARY KEY(`id`), 
              KEY `IDX_290FA7A537D8AD65` (`email_account_id`),
              UNIQUE KEY `UNIQ_290FA7A5ED3E8EA5` (`blob_id`),
              CONSTRAINT `FK_290FA7A537D8AD65` 
                FOREIGN KEY (`email_account_id`) REFERENCES `email_accounts` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_290FA7A5ED3E8EA5` 
                FOREIGN KEY (`blob_id`) REFERENCES `blobs` (`id`) ON DELETE CASCADE 
            ) ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci
        ");
    }

    /**
     * Alter tables required for this build
     */
    public function runAlters()
    {
        // Alter table `email_sources`, add `email_account_log_id` field and relation
        $this->execDbQuery('default', "
            ALTER TABLE `email_sources` 
            ADD `email_account_log_id` INT(11) DEFAULT NULL,
            ADD KEY `IDX_6F9D0D3D15AC8F3C` (`email_account_log_id`),
            ADD CONSTRAINT `FK_6F9D0D3D15AC8F3C` 
              FOREIGN KEY (`email_account_log_id`) REFERENCES `email_account_logs` (`id`) ON DELETE SET NULL
        ");
    }

    /**
     * Additional routines required for this build
     */
    public function run()
    {
        // nothing here
    }
}
