<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110407022443 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('New tables');

		try {
			App::getDb()->exec("
				CREATE TABLE articles (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, language_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, excerpt VARCHAR(1000) NOT NULL, content LONGTEXT NOT NULL, view_count INT NOT NULL, total_rating INT NOT NULL, num_ratings INT NOT NULL, is_published TINYINT(1) NOT NULL, display_order INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_BFDD3168217BBB47 (person_id), INDEX IDX_BFDD316882F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB
			");
			App::getDb()->exec("CREATE TABLE article_to_categories (article_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_9A1B4BB07294869C (article_id), INDEX IDX_9A1B4BB012469DE2 (category_id), PRIMARY KEY(article_id, category_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE article_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, is_book TINYINT(1) NOT NULL, INDEX IDX_62A97E9727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE comments_article (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, person_id INT DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(15) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_FF3FDAA7294869C (article_id), INDEX IDX_FF3FDAA217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE labels_articles (article_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_2F30AF707294869C (article_id), PRIMARY KEY(article_id, label)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
