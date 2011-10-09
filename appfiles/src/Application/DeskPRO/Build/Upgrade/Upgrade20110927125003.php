<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110927125003 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add facebook source with sample key');

		try {
			App::getOrm()->exec("ALTER TABLE  `news_comments` ADD  `validating` VARCHAR( 35 ) NULL DEFAULT NULL AFTER  `status`");
			App::getOrm()->exec("ALTER TABLE  `article_comments` ADD  `validating` VARCHAR( 35 ) NULL DEFAULT NULL AFTER  `status`");
			App::getOrm()->exec("ALTER TABLE  `idea_comments` ADD  `validating` VARCHAR( 35 ) NULL DEFAULT NULL AFTER  `status`");
			App::getOrm()->exec("ALTER TABLE  `download_comments` ADD  `validating` VARCHAR( 35 ) NULL DEFAULT NULL AFTER  `status`");
			App::getOrm()->persist($usersource);
			App::getOrm()->flush();

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
