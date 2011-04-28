<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110428053120 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add tmp_data table');

		try {
			App::getDb()->exec("CREATE TABLE tmp_data (id INT AUTO_INCREMENT NOT NULL, auth VARCHAR(15) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', date_created DATETIME NOT NULL, date_expire DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
