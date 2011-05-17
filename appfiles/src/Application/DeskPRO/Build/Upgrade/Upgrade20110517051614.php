<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110517051614 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add plugin_listeners');

		try {
			App::getDb()->exec("CREATE TABLE plugin_listeners (id INT AUTO_INCREMENT NOT NULL, plugin_id VARCHAR(255) DEFAULT NULL, event_name VARCHAR(255) DEFAULT NULL, event_options LONGTEXT NOT NULL COMMENT '(DC2Type:array)', description VARCHAR(255) NOT NULL, run_order INT NOT NULL, listener_class VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_FEEE2572EC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
