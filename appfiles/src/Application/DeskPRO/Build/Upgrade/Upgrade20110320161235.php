<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110320161235 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add banning tables');

		try {
			App::getDb()->exec("CREATE TABLE ban_ips (banned_ip VARCHAR(100) NOT NULL, ip_start BIGINT NOT NULL, ip_end BIGINT NOT NULL, PRIMARY KEY(banned_ip)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE ban_emails (banned_email VARCHAR(255) NOT NULL, PRIMARY KEY(banned_email)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
