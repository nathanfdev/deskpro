<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111029022429 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `article_comments` ADD  `is_reviewed` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `status`");
			App::getDb()->exec("ALTER TABLE  `download_comments` ADD  `is_reviewed` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `status`");
			App::getDb()->exec("ALTER TABLE  `idea_comments` ADD  `is_reviewed` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `status`");
			App::getDb()->exec("ALTER TABLE  `news_comments` ADD  `is_reviewed` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `status`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
