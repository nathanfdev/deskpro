<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\Build\Upgrader;

// A dummy class used to test the upgrade system
class Upgrade20110111114200 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln("Some information here");
		$this->output->writeln(__CLASS__ . ' ' . __LINE__);

		return Upgrader::STEP_DONE;
	}
}