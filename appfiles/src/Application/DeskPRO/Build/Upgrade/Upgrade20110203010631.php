<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110203010631 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Change handler_class on custom def tables to nullable');

		try {
			App::getDb()->exec("ALTER TABLE  `custom_def_ticket` CHANGE  `handler_class`  `handler_class` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
			App::getDb()->exec("ALTER TABLE  `custom_def_people` CHANGE  `handler_class`  `handler_class` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Change parent indexes on custom def tables to indexes instead of uniques');

		try {
			App::getDb()->exec("ALTER TABLE  `custom_def_ticket` DROP INDEX  `custom_def_ticket_parent_id_uniq` , ADD INDEX  `custom_def_ticket_parent_id_uniq` (  `parent_id` )");
			App::getDb()->exec("ALTER TABLE  `custom_def_people` DROP INDEX  `custom_def_people_parent_id_uniq` , ADD INDEX  `custom_def_people_parent_id_uniq` (  `parent_id` )");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step3()
	{
		$this->output->writeln('Create custom_def_ticket_rules table');

		try {
			App::getDb()->exec("CREATE TABLE custom_def_ticket_rules (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, conds LONGTEXT NOT NULL, actions LONGTEXT NOT NULL, run_order INT NOT NULL, INDEX custom_def_ticket_rules_department_id_idx (department_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
