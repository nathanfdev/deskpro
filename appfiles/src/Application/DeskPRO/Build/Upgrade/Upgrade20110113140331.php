<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110113140331 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->write('Add log_items table');

		try {
			$db = App::getDb();
			$db->exec("
				CREATE TABLE `log_items` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `log_name` varchar(25) NOT NULL,
				  `session_name` varchar(25) DEFAULT NULL,
				  `flag` varchar(50) DEFAULT NULL,
				  `priority` int(11) NOT NULL,
				  `priority_name` varchar(25) NOT NULL,
				  `message` varchar(1000) NOT NULL,
				  `data` longtext,
				  `date_created` datetime NOT NULL,
				  PRIMARY KEY (`id`),
				  KEY `log_name_idx` (`log_name`,`session_name`),
				  KEY `flag_idx` (`flag`)
				) ENGINE=InnoDB
			");
		} catch (\Exception $e) {
			$this->output->write("FAILED: " . $e->getMessage());
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
