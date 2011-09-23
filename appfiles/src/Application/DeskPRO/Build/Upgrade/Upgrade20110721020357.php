<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110721020357 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				CREATE TABLE content_subscriptions (
					id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL,
					article_id INT DEFAULT NULL,
					download_id INT DEFAULT NULL,
					idea_id INT DEFAULT NULL,
					news_id INT DEFAULT NULL,
					use_email TINYINT(1) NOT NULL,
					last_dismiss_date DATETIME NOT NULL,
					last_email_date DATETIME NOT NULL,
					updated_date DATETIME NOT NULL,
					INDEX IDX_5FADAC10217BBB47 (person_id),
					INDEX IDX_5FADAC107294869C (article_id),
					INDEX IDX_5FADAC10C667AEAB (download_id),
					INDEX IDX_5FADAC105B6FEF7D (idea_id),
					INDEX IDX_5FADAC10B5A459A0 (news_id),
					PRIMARY KEY(id)
				) ENGINE = InnoDB
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
