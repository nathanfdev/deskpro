<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110830092325 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `idea_revisions`");
			App::getDb()->exec("DROP TABLE IF EXISTS `ideas`");
			App::getDb()->exec("CREATE TABLE ideas (id INT AUTO_INCREMENT NOT NULL, status_category_id INT DEFAULT NULL, category_id INT DEFAULT NULL, person_id INT DEFAULT NULL, language_id INT DEFAULT NULL, hidden_status VARCHAR(15) DEFAULT NULL, popularity INT NOT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, view_count INT NOT NULL, total_rating INT NOT NULL, num_ratings INT NOT NULL, status VARCHAR(15) NOT NULL, date_created DATETIME NOT NULL, date_published DATETIME DEFAULT NULL, INDEX IDX_1DB2F1DE169CE813 (status_category_id), INDEX IDX_1DB2F1DE12469DE2 (category_id), INDEX IDX_1DB2F1DE217BBB47 (person_id), INDEX IDX_1DB2F1DE82F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE ideas ADD FOREIGN KEY (status_category_id) REFERENCES idea_status_categories(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE ideas ADD FOREIGN KEY (category_id) REFERENCES idea_categories(id)");
			App::getDb()->exec("ALTER TABLE ideas ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE ideas ADD FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE");

			App::getDb()->exec("CREATE TABLE idea_revisions (id INT AUTO_INCREMENT NOT NULL, idea_id INT DEFAULT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_3B7600685B6FEF7D (idea_id), INDEX IDX_3B760068217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE idea_revisions ADD FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE idea_revisions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
