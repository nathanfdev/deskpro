<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111216144516 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");
			App::getDb()->exec("DROP TABLE `email_gateways`");
			App::getDb()->exec("CREATE TABLE email_gateways (id INT AUTO_INCREMENT NOT NULL, title TINYTEXT NOT NULL, connection_type VARCHAR(15) NOT NULL, connection_options LONGTEXT NOT NULL COMMENT '(DC2Type:array)', gateway_type VARCHAR(15) NOT NULL, is_enabled TINYINT(1) NOT NULL, date_last_check DATETIME DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 1");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
