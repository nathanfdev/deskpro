<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110519052118 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Rename ');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS article_pending_edits");
			App::getDb()->exec("CREATE TABLE article_validating_edits (article_id INT NOT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, excerpt VARCHAR(1000) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_40983882217BBB47 (person_id), PRIMARY KEY(article_id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
