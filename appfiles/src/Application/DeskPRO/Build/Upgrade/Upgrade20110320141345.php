<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110320141345 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add Twitter Status Notes Table');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS twitter_statuses_notes");
			App::getDb()->exec("CREATE TABLE twitter_statuses_notes (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, person_id INT DEFAULT NULL, text VARCHAR(4000) NOT NULL, date_created DATETIME NOT NULL, INDEX twitter_statuses_notes_status_id_idx (status_id), INDEX twitter_statuses_notes_person_id_idx (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE twitter_statuses_notes ADD FOREIGN KEY (status_id) REFERENCES twitter_statuses(id)");
			App::getDb()->exec("ALTER TABLE twitter_statuses_notes ADD FOREIGN KEY (person_id) REFERENCES people(id)");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
