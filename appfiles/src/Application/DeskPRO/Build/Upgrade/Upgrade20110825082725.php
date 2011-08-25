<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110825082725 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE article_attachments (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, person_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_DD4790B17294869C (article_id), INDEX IDX_DD4790B1217BBB47 (person_id), INDEX IDX_DD4790B1ED3E8EA5 (blob_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE article_attachments ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE article_attachments ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE article_attachments ADD FOREIGN KEY (blob_id) REFERENCES blobs(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
