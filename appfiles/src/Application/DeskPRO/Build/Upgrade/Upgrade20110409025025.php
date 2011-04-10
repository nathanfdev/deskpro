<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110409025025 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Idea tables');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS idea_categories");
			App::getDb()->exec("DROP TABLE IF EXISTS ideas");
			App::getDb()->exec("DROP TABLE IF EXISTS idea_votes");
			App::getDb()->exec("CREATE TABLE ideas (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(15) NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, num_votes INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_1DB2F1DE217BBB47 (person_id), INDEX IDX_1DB2F1DE12469DE2 (category_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_E4FA1F8F727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_votes (id INT AUTO_INCREMENT NOT NULL, idea_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, num_votes INT NOT NULL, is_returned TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_104C491A5B6FEF7D (idea_id), INDEX IDX_104C491A217BBB47 (person_id), INDEX IDX_104C491A70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE visitors (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, auth VARCHAR(15) NOT NULL, ip_address VARCHAR(80) NOT NULL, user_agent VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, date_last DATETIME NOT NULL, INDEX IDX_7B74A43F217BBB47 (person_id), INDEX date_last_idx (date_last), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
