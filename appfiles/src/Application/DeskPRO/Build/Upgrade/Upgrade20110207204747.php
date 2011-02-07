<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110207204747 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreate custom_data_person table (part 1: drop)');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `custom_data_person`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Recreate custom_data_person table (part 2: create)');

		try {
			App::getDb()->exec("CREATE TABLE custom_data_person (id INT AUTO_INCREMENT NOT NULL, field_id INT DEFAULT NULL, person_id INT DEFAULT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX custom_data_person_field_id_idx (field_id), INDEX custom_data_person_person_id_idx (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
