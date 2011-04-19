<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110419065937 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create article_ratings');

		try {
			App::getDb()->exec("CREATE TABLE article_ratings (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, rating INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_2364437E7294869C (article_id), INDEX IDX_2364437E217BBB47 (person_id), INDEX IDX_2364437E70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
