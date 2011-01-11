<?php

namespace Application\DeskPRO\Build\Upgrade;
use \Application\DeskPRO\Build\Upgrader;

// A dummy class used to test the upgrade system
class Upgrade20110111114201 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->write(__CLASS__ . ' ' . __LINE__);
		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->write(__CLASS__ . ' ' . __LINE__);
		return Upgrader::STEP_DONE;
	}

	public function step3()
	{
		$this->output->write(__CLASS__ . ' ' . __LINE__);

		if (mt_rand(1,10) < 5) {
			return Upgrader::STEP_FAILED;
		} else {
			return Upgrader::STEP_DONE;
		}
	}

	public function step4()
	{
		$this->output->write(__CLASS__ . ' ' . __LINE__);
	}
}