<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110909095001 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Repair categories');

		try {
			foreach (array('ArticleCategory', 'IdeaCategory', 'NewsCategory', 'DownloadCategory', 'Product') as $name) {
				$name = "DeskPRO:$name";
				$er = App::getEntityRepository($name);
				$er->repair();
			}
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
