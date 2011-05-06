<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110506012433 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Change tickets_participants table');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets_participants` ADD  `person_email_id` INT NULL DEFAULT NULL AFTER  `person_id`");
			App::getDb()->exec("
				ALTER TABLE  `dp_400`.`tickets_participants` DROP INDEX  `tickets_participants_person_id_uniq` ,
				ADD INDEX  `tickets_participants_person_id_uniq` (  `person_id` )
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
