<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110410183956 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Comment tables');

		try {
			App::getDb()->exec("CREATE TABLE article_comments (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, person_id INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_A7662417294869C (article_id), INDEX IDX_A766241217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE news_comments (id INT AUTO_INCREMENT NOT NULL, news_id INT DEFAULT NULL, person_id INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_16A0357BB5A459A0 (news_id), INDEX IDX_16A0357B217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_comments (id INT AUTO_INCREMENT NOT NULL, idea_id INT DEFAULT NULL, person_id INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_25B753935B6FEF7D (idea_id), INDEX IDX_25B75393217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
