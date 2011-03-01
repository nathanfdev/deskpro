<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110301040051 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreate triggers');

		try {
			App::getDb()->exec('DROP TRIGGER IF EXISTS ticket_search_insert');
			App::getDb()->exec('DROP TRIGGER IF EXISTS ticket_search_update');
			App::getDb()->exec('DROP TRIGGER IF EXISTS ticket_search_delete');
			App::getDb()->exec('DROP TRIGGER IF EXISTS ticket_search_message_insert');

			$data_reader = new \Application\DeskPRO\Install\InstallData('triggers.sql');
			foreach ($data_reader as $k => $sql) {
				$this->output->writeln("Trigger ... $k");
				App::getDb()->exec($sql);
			}
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
