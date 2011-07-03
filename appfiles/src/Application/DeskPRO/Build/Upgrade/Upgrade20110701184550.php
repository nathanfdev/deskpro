<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110701184549 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("TRUNCATE TABLE ticket_triggers");
			$install_data = new \Application\DeskPRO\Install\InstallData('data.php');
			foreach ($install_data->getAllForTag('create_ticket_trigger') as $name => $code) {
				$this->output->writeln("<info>Creating ticket trigger {$name}</info>");
				eval($code);
			}
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
