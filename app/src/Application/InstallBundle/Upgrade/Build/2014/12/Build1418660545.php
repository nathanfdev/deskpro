<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1418660545 extends AbstractBuild
{
    public function run()
    {
		$this->out("Add sendmail_sources table");
		$this->execMutateSql("CREATE TABLE sendmail_sources (id INT AUTO_INCREMENT NOT NULL, blob_id INT DEFAULT NULL, email_account_id INT DEFAULT NULL, log_blob_id INT DEFAULT NULL, ref VARCHAR(100) NOT NULL, context_type VARCHAR(100) NOT NULL, context_id INT NOT NULL, context_info LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', headers LONGTEXT NOT NULL, header_to LONGTEXT NOT NULL, header_from LONGTEXT NOT NULL, header_subject LONGTEXT NOT NULL, from_email LONGTEXT NOT NULL, to_emails LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', cc_emails LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', bcc_emails LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', status VARCHAR(80) NOT NULL, date_status DATETIME NOT NULL, date_sent DATETIME DEFAULT NULL, date_next_attempt DATETIME DEFAULT NULL, error_code VARCHAR(80) NOT NULL, date_created DATETIME DEFAULT NULL, exec_count INT NOT NULL, INDEX IDX_9195FF45ED3E8EA5 (blob_id), INDEX IDX_9195FF4537D8AD65 (email_account_id), INDEX IDX_9195FF45D5F3B632 (log_blob_id), INDEX status_idx (status), UNIQUE INDEX ref_idx (ref), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE sendmail_sources ADD CONSTRAINT FK_9195FF45ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE sendmail_sources ADD CONSTRAINT FK_9195FF4537D8AD65 FOREIGN KEY (email_account_id) REFERENCES email_accounts (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE sendmail_sources ADD CONSTRAINT FK_9195FF45D5F3B632 FOREIGN KEY (log_blob_id) REFERENCES blobs (id) ON DELETE SET NULL");
    }
}