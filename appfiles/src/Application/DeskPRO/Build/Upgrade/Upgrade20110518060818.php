<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110518060818 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreate plugins table');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS plugins");
			App::getDb()->exec("CREATE TABLE plugins (id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, version VARCHAR(100) NOT NULL, package_class VARCHAR(255) NOT NULL, package_class_file VARCHAR(255) NOT NULL, resources_path VARCHAR(255) NOT NULL, autoload_paths LONGTEXT NOT NULL COMMENT '(DC2Type:array)', date_created DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
