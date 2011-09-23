<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110822145905 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `article_revisions`");
			App::getDb()->exec("CREATE TABLE article_revisions (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_538472A17294869C (article_id), INDEX IDX_538472A1217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE article_revisions ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE article_revisions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");

			App::getDb()->exec("DROP TABLE IF EXISTS `news_revisions`");
			App::getDb()->exec("CREATE TABLE news_revisions (id INT AUTO_INCREMENT NOT NULL, news_id INT DEFAULT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_95947D44B5A459A0 (news_id), INDEX IDX_95947D44217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE news_revisions ADD FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE news_revisions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");

			App::getDb()->exec("DROP TABLE IF EXISTS `download_revisions`");
			App::getDb()->exec("CREATE TABLE download_revisions (id INT AUTO_INCREMENT NOT NULL, download_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_483B9D66C667AEAB (download_id), INDEX IDX_483B9D66ED3E8EA5 (blob_id), INDEX IDX_483B9D66217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE download_revisions ADD FOREIGN KEY (download_id) REFERENCES downloads(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE download_revisions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE download_revisions ADD FOREIGN KEY (blob_id) REFERENCES blobs(id)");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
