<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110909095000 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Update categories');

		try {
			//App::getDb()->exec("ALTER TABLE  `article_categories` DROP  `lft` , DROP  `rgt`");
			//App::getDb()->exec("ALTER TABLE  `idea_categories` DROP  `lft` , DROP  `rgt`");
			//App::getDb()->exec("ALTER TABLE  `news_categories` DROP  `lft` , DROP  `rgt`");
			App::getDb()->exec("ALTER TABLE  `download_categories` DROP  `lft` , DROP  `rgt`");
			App::getDb()->exec("ALTER TABLE  `products` DROP  `lft` , DROP  `rgt`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
