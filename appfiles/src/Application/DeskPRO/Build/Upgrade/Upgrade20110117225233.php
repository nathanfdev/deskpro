<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110117225233 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->write('Alter departments table');

		try {
			App::getDb()->exec("ALTER TABLE  `departments` ADD  `parent_id` INT NULL DEFAULT NULL");
		} catch (\Exception $e) {
			$this->output->write("Error: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->write('Adding example children');

		try {
			
			$count = 0;
			$existing_deps = App::getDb()->fetchAll("SELECT id FROM departments ORDER BY id ASC");

			App::getDb()->beginTransaction();
			foreach ($existing_deps as $dep) {
				App::getDb()->insert('departments', array(
					'title' => 'Child ' . (++$count),
					'parent_id' => $dep['id']
				));
			}
			App::getDb()->commit();

		} catch (\Exception $e) {
			App::getDb()->rollBack();
			$this->output->write("Error: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
