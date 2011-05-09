<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110509170051 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add display order to all sorts of wonderful models');

		try {
			$tables = array(
				'departments', 'products', 'article_categories',
				'download_categories', 'idea_categories', 'news_categories',
				'ticket_workflows'
			);
			foreach ($tables as $t) {
				App::getDb()->exec("ALTER TABLE  `$t` ADD  `display_order` INT NOT NULL DEFAULT  '0'");
			}
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
