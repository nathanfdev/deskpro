<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110519160449 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add glossary words');

		try {
			App::getDb()->exec("CREATE TABLE glossary_word (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
