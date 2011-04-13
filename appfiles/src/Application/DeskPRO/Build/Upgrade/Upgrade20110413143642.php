<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110413143642 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create email_from');

		try {
			App::getDb()->exec("CREATE TABLE email_from (id INT AUTO_INCREMENT NOT NULL, name TINYTEXT NOT NULL, address TINYTEXT NOT NULL, transport_class VARCHAR(80) NOT NULL, transport_options LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
