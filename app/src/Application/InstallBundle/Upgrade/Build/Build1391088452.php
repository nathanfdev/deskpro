<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

class Build1391088452 extends AbstractBuild
{
	public function run()
	{
		$this->out("New app tables, rename old plugin relationships");
		$this->execMutateSql("CREATE TABLE app_assets (id INT AUTO_INCREMENT NOT NULL, package_name VARCHAR(255) DEFAULT NULL, blob_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, tag VARCHAR(50) DEFAULT NULL, metadata LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_9CD2100EE56E1BCE (package_name), UNIQUE INDEX UNIQ_9CD2100EED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE app_instances (id INT AUTO_INCREMENT NOT NULL, package_name VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, secret_key VARCHAR(40) NOT NULL, auth_key VARCHAR(40) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_D2485F5CE56E1BCE (package_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE app_packages (name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, author_name VARCHAR(255) NOT NULL, author_email VARCHAR(255) NOT NULL, author_link VARCHAR(255) NOT NULL, api_version INT NOT NULL, version INT NOT NULL, version_name VARCHAR(100) NOT NULL, native_name VARCHAR(255) DEFAULT NULL, is_single TINYINT(1) NOT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE jira_issues (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, issue_id INT NOT NULL, created INT NOT NULL, INDEX IDX_88385CE2700047D2 (ticket_id), INDEX issue_id_idx (issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE app_assets ADD CONSTRAINT FK_9CD2100EE56E1BCE FOREIGN KEY (package_name) REFERENCES app_packages (name) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE app_assets ADD CONSTRAINT FK_9CD2100EED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE app_instances ADD CONSTRAINT FK_D2485F5CE56E1BCE FOREIGN KEY (package_name) REFERENCES app_packages (name) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE jira_issues ADD CONSTRAINT FK_88385CE2700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE SET NULL");

		$this->execMutateSql('ALTER TABLE `custom_def_article` DROP FOREIGN KEY `FK_B651E6F4EC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_chat` DROP FOREIGN KEY `FK_2DE86CE5EC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_feedback` DROP FOREIGN KEY `FK_CC9CDDD8EC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_organizations` DROP FOREIGN KEY `FK_240601E7EC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_people` DROP FOREIGN KEY `FK_4840CFDAEC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_products` DROP FOREIGN KEY `FK_AD0FC3DAEC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_ticket` DROP FOREIGN KEY `FK_F7F6085FEC942BCF`');
		$this->execMutateSql('ALTER TABLE `ticket_trigger_plugin_actions` DROP FOREIGN KEY `FK_1D905890EC942BCF`');
		$this->execMutateSql('ALTER TABLE `usersource_plugins` DROP FOREIGN KEY `FK_E484A367EC942BCF`');

		$this->execMutateSql('ALTER TABLE `custom_def_article` DROP INDEX `IDX_B651E6F4EC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_chat` DROP INDEX `IDX_2DE86CE5EC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_feedback` DROP INDEX `IDX_CC9CDDD8EC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_organizations` DROP INDEX `IDX_240601E7EC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_people` DROP INDEX `IDX_4840CFDAEC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_products` DROP INDEX `IDX_AD0FC3DAEC942BCF`');
		$this->execMutateSql('ALTER TABLE `custom_def_ticket` DROP INDEX `IDX_F7F6085FEC942BCF`');
		$this->execMutateSql('ALTER TABLE `ticket_trigger_plugin_actions` DROP INDEX `IDX_1D905890EC942BCF`');
		$this->execMutateSql('ALTER TABLE `usersource_plugins` DROP INDEX `IDX_E484A367EC942BCF`');

		$this->execMutateSql("ALTER TABLE custom_def_article DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_article ADD CONSTRAINT FK_B651E6F47987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
		$this->execMutateSql("CREATE INDEX IDX_B651E6F47987212D ON custom_def_article (app_id)");

		$this->execMutateSql("ALTER TABLE custom_def_chat DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_chat ADD CONSTRAINT FK_2DE86CE57987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
		$this->execMutateSql("CREATE INDEX IDX_2DE86CE57987212D ON custom_def_chat (app_id)");

		$this->execMutateSql("ALTER TABLE custom_def_feedback DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_feedback ADD CONSTRAINT FK_CC9CDDD87987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
		$this->execMutateSql("CREATE INDEX IDX_CC9CDDD87987212D ON custom_def_feedback (app_id)");

		$this->execMutateSql("ALTER TABLE custom_def_organizations DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_organizations ADD CONSTRAINT FK_240601E77987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
		$this->execMutateSql("CREATE INDEX IDX_240601E77987212D ON custom_def_organizations (app_id)");

		$this->execMutateSql("ALTER TABLE custom_def_people DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_people ADD CONSTRAINT FK_4840CFDA7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
		$this->execMutateSql("CREATE INDEX IDX_4840CFDA7987212D ON custom_def_people (app_id)");

		$this->execMutateSql("ALTER TABLE custom_def_products DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_products ADD CONSTRAINT FK_AD0FC3DA7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
		$this->execMutateSql("CREATE INDEX IDX_AD0FC3DA7987212D ON custom_def_products (app_id)");

		$this->execMutateSql("ALTER TABLE custom_def_ticket DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_ticket ADD CONSTRAINT FK_F7F6085F7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
		$this->execMutateSql("CREATE INDEX IDX_F7F6085F7987212D ON custom_def_ticket (app_id)");

		$this->execMutateSql("ALTER TABLE ticket_trigger_plugin_actions DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE ticket_trigger_plugin_actions ADD CONSTRAINT FK_1D9058907987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_1D9058907987212D ON ticket_trigger_plugin_actions (app_id)");

		$this->execMutateSql("ALTER TABLE usersource_plugins DROP plugin_id, ADD app_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE usersource_plugins ADD CONSTRAINT FK_E484A3677987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_E484A3677987212D ON usersource_plugins (app_id)");
	}
}