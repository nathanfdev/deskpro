<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110701072439 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("SET foreign_key_checks = 0");

			App::getDb()->exec("TRUNCATE TABLE `tickets_participants`");
			App::getDb()->exec("ALTER TABLE `tickets_participants` DROP `code`");
			App::getDb()->exec("ALTER TABLE  `tickets_participants` ADD  `access_code_id` INT NOT NULL AFTER  `person_email_id`");

			App::getDb()->exec("DROP TABLE `ticket_access_codes`");
			App::getDb()->exec("
				CREATE TABLE ticket_access_codes(
					id INT AUTO_INCREMENT NOT NULL ,
					ticket_id INT DEFAULT NULL ,
					person_id INT DEFAULT NULL ,
					auth VARCHAR( 5 ) NOT NULL ,
					INDEX IDX_CCEE41B5700047D2( ticket_id ) ,
					INDEX IDX_CCEE41B5217BBB47( person_id ) ,
					PRIMARY KEY ( id )
				) ENGINE = INNODB
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
