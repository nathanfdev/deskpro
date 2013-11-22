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

class Build1385119820 extends AbstractBuild
{
	public function run()
	{
		$this->execMutateSql("CREATE TABLE plugin_assets (id INT AUTO_INCREMENT NOT NULL, plugin_def_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, INDEX IDX_CB93A328150E793 (plugin_def_id), UNIQUE INDEX UNIQ_CB93A32ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE plugin_defs (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, author_name VARCHAR(255) NOT NULL, author_email VARCHAR(255) NOT NULL, author_link VARCHAR(255) NOT NULL, api_version INT NOT NULL, version INT NOT NULL, version_name VARCHAR(100) NOT NULL, native_name VARCHAR(255) DEFAULT NULL, is_single TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE plugin_assets ADD CONSTRAINT FK_CB93A328150E793 FOREIGN KEY (plugin_def_id) REFERENCES plugin_defs (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE plugin_assets ADD CONSTRAINT FK_CB93A32ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE");

		foreach (array(
					 'custom_def_article',
					 'custom_def_chat',
					 'custom_def_feedback',
					 'custom_def_organizations',
					 'custom_def_people',
					 'custom_def_products',
					 'custom_def_ticket',
					 'ticket_trigger_plugin_actions',
					 'usersource_plugins',
					 'widgets',
				 ) as $table) {
			$fks = $this->container->getDb()->getSchemaManager()->listTableForeignKeys($table);
			foreach ($fks as $fk) {
				if (in_array('plugin_id', $fk->getColumns())) {
					$this->execMutateSql("ALTER TABLE $table DROP FOREIGN KEY {$fk->getName()}");
				}
			}

			$indexes = $this->container->getDb()->getSchemaManager()->listTableIndexes($table);
			foreach ($indexes as $idx) {
				if (in_array('plugin_id', $idx->getColumns())) {
					$this->execMutateSql("ALTER TABLE $table DROP KEY {$idx->getName()}");
				}
			}
		}

		$this->execMutateSql("ALTER TABLE plugins ADD plugin_def_id INT DEFAULT NULL, DROP description, DROP version, DROP package_class, DROP package_class_file, DROP resources_path, DROP enabled, CHANGE id id INT AUTO_INCREMENT NOT NULL");
		$this->execMutateSql("ALTER TABLE plugins ADD CONSTRAINT FK_EC85F6718150E793 FOREIGN KEY (plugin_def_id) REFERENCES plugin_defs (id) ON DELETE CASCADE");

		$this->execMutateSql("ALTER TABLE custom_def_article CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_article ADD CONSTRAINT FK_B651E6F4EC942BCF FOREIGN KEY (plugin_id) REFERENCES plugins (id) ON DELETE SET NULL");
		$this->execMutateSql("ALTER TABLE custom_def_chat CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_chat ADD CONSTRAINT FK_2DE86CE5EC942BCF FOREIGN KEY (plugin_id) REFERENCES plugins (id) ON DELETE SET NULL");
		$this->execMutateSql("ALTER TABLE custom_def_feedback CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_feedback ADD CONSTRAINT FK_CC9CDDD8EC942BCF FOREIGN KEY (plugin_id) REFERENCES plugins (id) ON DELETE SET NULL");
		$this->execMutateSql("ALTER TABLE custom_def_organizations CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_organizations ADD CONSTRAINT FK_240601E7EC942BCF FOREIGN KEY (plugin_id) REFERENCES plugins (id) ON DELETE SET NULL");
		$this->execMutateSql("ALTER TABLE custom_def_people CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_people ADD CONSTRAINT FK_4840CFDAEC942BCF FOREIGN KEY (plugin_id) REFERENCES plugins (id) ON DELETE SET NULL");
		$this->execMutateSql("ALTER TABLE custom_def_products CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_products ADD CONSTRAINT FK_AD0FC3DAEC942BCF FOREIGN KEY (plugin_id) REFERENCES plugins (id) ON DELETE SET NULL");
		$this->execMutateSql("ALTER TABLE custom_def_ticket CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE custom_def_ticket ADD CONSTRAINT FK_F7F6085FEC942BCF FOREIGN KEY (plugin_id) REFERENCES plugins (id) ON DELETE SET NULL");
		$this->execMutateSql("ALTER TABLE ticket_trigger_plugin_actions CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE usersource_plugins CHANGE plugin_id plugin_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE widgets CHANGE plugin_id plugin_id INT DEFAULT NULL");
	}
}