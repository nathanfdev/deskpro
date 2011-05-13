<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110513010148 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE ticket_page_display (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, zone VARCHAR(50) DEFAULT NULL, section VARCHAR(50) DEFAULT NULL, handler_class VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', INDEX IDX_491216EAAE80F5DF (department_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
