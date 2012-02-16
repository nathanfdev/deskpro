<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110927125002 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add facebook source with sample key');

		try {
			$usersource = new \Application\DeskPRO\Entity\Usersource();
			$usersource->title = 'Facebook';
			$usersource->adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\Facebook';
			$usersource->options = array(
				'app_key' => '113084412087018',
				'app_secret' => '95b780ea94a5797af2629661bcb0f725',
			);

			App::getOrm()->persist($usersource);
			App::getOrm()->flush();

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
