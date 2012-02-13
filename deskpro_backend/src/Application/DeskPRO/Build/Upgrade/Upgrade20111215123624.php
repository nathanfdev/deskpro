<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111215123624 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				CREATE TABLE `email_transports` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `title` varchar(255) NOT NULL,
				  `match_type` varchar(15) NOT NULL,
				  `match_pattern` varchar(15) NOT NULL,
				  `transport_type` varchar(80) NOT NULL,
				  `transport_options` longtext NOT NULL COMMENT '(DC2Type:array)',
				  `backup_transport_type` varchar(80) NOT NULL DEFAULT '',
				  `backup_transport_options` longtext NOT NULL COMMENT '(DC2Type:array)',
				  `run_order` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
