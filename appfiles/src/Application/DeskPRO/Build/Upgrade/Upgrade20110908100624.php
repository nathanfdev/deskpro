<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110908100624 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add is_hold to tickets');

		try {
			App::getDb()->exec("ALTER TABLE  `tickets` ADD  `is_hold` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `hidden_status`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		App::getDb()->beginTransaction();

		$this->output->writeln('Delete existing system filters');

		App::getDb()->exec("
			DELETE FROM ticket_filters
			WHERE sys_name IS NOT NULL
		");

		$this->output->writeln('Recreate them');

		$install = new \Application\DeskPRO\Install\InstallData('data.php');
		foreach ($install->getAllForTag('create_filter') as $filter_code) {
			eval($filter_code);
		}

		App::getDb()->commit();
	}

	public function step3()
	{
		$this->output->writeln('Set a few tickets on hold for demo');

		App::getDb()->exec("UPDATE tickets SET is_hold = 0");
		App::getDb()->exec("UPDATE tickets SET is_hold = 1 WHERE status = 'open' ORDER BY RAND() LIMIT 32");
	}
}
