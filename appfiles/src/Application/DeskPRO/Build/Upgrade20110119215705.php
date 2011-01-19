<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110119215705 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->write('My Upgrade Code');
		return Upgrader::STEP_DONE;
	}
}