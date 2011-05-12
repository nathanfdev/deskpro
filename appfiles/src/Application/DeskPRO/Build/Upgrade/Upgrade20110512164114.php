<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110512164114 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add plugin stuff');

		try {
			App::getDb()->exec("ALTER TABLE  `custom_def_people` ADD  `plugin_id` INT NULL DEFAULT NULL");
			App::getDb()->exec("ALTER TABLE  `custom_def_ticket` ADD  `plugin_id` INT NULL DEFAULT NULL");
			App::getDb()->exec("ALTER TABLE  `custom_def_organizations` ADD  `plugin_id` INT NULL DEFAULT NULL");
			App::getDb()->exec("ALTER TABLE  `custom_def_people` ADD  `custom_template_id` INT NULL DEFAULT NULL");
			App::getDb()->exec("ALTER TABLE  `custom_def_ticket` ADD  `custom_template_id` INT NULL DEFAULT NULL");
			App::getDb()->exec("ALTER TABLE  `custom_def_organizations` ADD  `custom_template_id` INT NULL DEFAULT NULL");
			App::getDb()->exec("DROP TABLE IF EXISTS plugins");
			App::getDb()->exec("CREATE TABLE plugins (id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, version VARCHAR(100) NOT NULL, package_class VARCHAR(255) NOT NULL, package_class_file VARCHAR(255) NOT NULL, resources_path VARCHAR(255) NOT NULL, autoload_paths LONGTEXT NOT NULL COMMENT '(DC2Type:array)', date_created DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE plugin_listeners (id INT AUTO_INCREMENT NOT NULL, plugin_id VARCHAR(255) DEFAULT NULL, event_name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) NOT NULL, run_order INT NOT NULL, listener_class VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_FEEE2572EC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
