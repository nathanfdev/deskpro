<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110203195952 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create widget table');

		try {
			App::getDb()->exec("CREATE TABLE widgets (id INT AUTO_INCREMENT NOT NULL, name_id VARCHAR(200) NOT NULL, assets_css LONGTEXT NOT NULL, assets_js LONGTEXT NOT NULL, section VARCHAR(200) NOT NULL, template_name VARCHAR(200) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
