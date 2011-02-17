<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110217175652 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE sendmail_queue (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, to_address VARCHAR(255) NOT NULL, attempts INT NOT NULL, date_created DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE sendmail_queue_part (id INT AUTO_INCREMENT NOT NULL, sendmail_queue_id INT DEFAULT NULL, data LONGTEXT NOT NULL, INDEX sendmail_queue_part_sendmail_queue_id_idx (sendmail_queue_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
