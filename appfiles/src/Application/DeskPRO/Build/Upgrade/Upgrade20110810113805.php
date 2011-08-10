<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110810113805 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Insert some default tags for tickets');

		try {
			$labels = App::getDb()->fetchAllCol("SELECT label FROM label_defs WHERE label_type = 'articles'");
			$q_parts = array();
			foreach ($labels as $l) {
				$q_parts[] = "('tickets', '$l')";
			}

			App::getDb()->exec("INSERT INTO label_defs (label_type, label) VALUES " . implode(', ', $q_parts));
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
