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
			App::getDb()->exec("DROP TABLE IF EXISTS `articles`");
			App::getDb()->exec("DROP TABLE IF EXISTS `article_categories`");
			App::getDb()->exec("DROP TABLE IF EXISTS `article_to_categories`");
			App::getDb()->exec("DROP TABLE IF EXISTS `downloads`");
			App::getDb()->exec("DROP TABLE IF EXISTS `download_categories`");
			App::getDb()->exec("DROP TABLE IF EXISTS `news`");
			App::getDb()->exec("DROP TABLE IF EXISTS `news_categories`");

			App::getDb()->exec("CREATE TABLE articles (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, language_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, excerpt VARCHAR(1000) NOT NULL, content LONGTEXT NOT NULL, view_count INT NOT NULL, total_rating INT NOT NULL, num_ratings INT NOT NULL, is_published TINYINT(1) NOT NULL, display_order INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_BFDD3168217BBB47 (person_id), INDEX IDX_BFDD316882F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE article_to_categories (article_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_9A1B4BB07294869C (article_id), INDEX IDX_9A1B4BB012469DE2 (category_id), PRIMARY KEY(article_id, category_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE article_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, is_book TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_62A97E9727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE downloads (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, person_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, num_downloads INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_4B73A4B512469DE2 (category_id), INDEX IDX_4B73A4B5217BBB47 (person_id), INDEX IDX_4B73A4B5ED3E8EA5 (blob_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE download_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_3317F15727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, is_published TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_1DD3995012469DE2 (category_id), INDEX IDX_1DD39950217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE news_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_D68C9111727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
