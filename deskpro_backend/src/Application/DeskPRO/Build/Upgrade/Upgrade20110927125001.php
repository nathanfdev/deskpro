<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110927125001 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add twitter source with sample key');

		try {
			$usersource = new \Application\DeskPRO\Entity\Usersource();
			$usersource->title = 'Twitter';
			$usersource->adapter_class = 'Application\\DeskPRO\\Usersource\\Adapter\\Twitter';
			$usersource->options = array(
				'consumer_key' => 'ZVTbT9LumEx85sezM3a6w',
				'consumer_secret' => 'ZJ6oL6HfoE5x4XVJUKhw3SWSPsxIFDUisYaPauB7K8',
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
