<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110520014155 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Article');

		try {
			App::getDb()->exec("ALTER TABLE  `articles` ADD  `date_published` DATETIME NULL DEFAULT NULL AFTER  `date_created`");
			App::getDb()->exec("RENAME TABLE `glossary_word` TO  `glossary_words`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
