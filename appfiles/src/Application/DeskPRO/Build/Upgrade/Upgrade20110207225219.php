<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110207225219 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create organization field tables');

		try {
			App::getDb()->exec("CREATE TABLE custom_def_organizations (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL, INDEX custom_def_organizations_parent_id_idx (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE custom_data_organizations (id INT AUTO_INCREMENT NOT NULL, field_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX custom_data_organizations_field_id_idx (field_id), INDEX custom_data_organizations_organization_id_idx (organization_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
