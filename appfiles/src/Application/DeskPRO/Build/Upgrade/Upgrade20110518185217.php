<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110518185217 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Changes to article');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS articles");
			App::getDb()->exec("DROP TABLE IF EXISTS article_categories");
			App::getDb()->exec("DROP TABLE IF EXISTS article_ratings");
			App::getDb()->exec("DROP TABLE IF EXISTS article_to_categories");
			App::getDb()->exec("
				CREATE TABLE article_to_categories (
					article_id INT NOT NULL,
					category_id INT NOT NULL,
					INDEX IDX_9A1B4BB07294869C (article_id),
					INDEX IDX_9A1B4BB012469DE2 (category_id),
					PRIMARY KEY(article_id,
					category_id)
				) ENGINE = InnoDB");
			App::getDb()->exec("
				CREATE TABLE article_pending_edits (
					article_id INT NOT NULL,
					person_id INT DEFAULT NULL,
					title VARCHAR(255) NOT NULL,
					excerpt VARCHAR(1000) NOT NULL,
					content LONGTEXT NOT NULL,
					INDEX IDX_FEA36E02217BBB47 (person_id),
					PRIMARY KEY(article_id)
				) ENGINE = InnoDB");
			App::getDb()->exec("
				CREATE TABLE article_to_product (
					article_id INT NOT NULL,
					product_id INT NOT NULL,
					INDEX IDX_610BE8D97294869C (article_id),
					INDEX IDX_610BE8D94584665A (product_id),
					PRIMARY KEY(article_id, product_id)
				) ENGINE = InnoDB");
			App::getDb()->exec("
				CREATE TABLE article_pending_create (
					id INT AUTO_INCREMENT NOT NULL,
					person_id INT DEFAULT NULL,
					ticket_id INT DEFAULT NULL,
					comment VARCHAR(1000) NOT NULL,
					date_created DATETIME NOT NULL,
					INDEX IDX_27A971C3217BBB47 (person_id),
					INDEX IDX_27A971C3700047D2 (ticket_id),
					PRIMARY KEY(id)
				) ENGINE = InnoDB");
			App::getDb()->exec("
				CREATE TABLE article_categories (
					id INT AUTO_INCREMENT NOT NULL,
					parent_id INT DEFAULT NULL,
					is_agent TINYINT(1) NOT NULL,
					is_book TINYINT(1) NOT NULL,
					template_suffix VARCHAR(100) DEFAULT NULL,
					title VARCHAR(255) NOT NULL,
					display_order INT NOT NULL,
					root INT DEFAULT NULL,
					depth INT NOT NULL,
					lft INT NOT NULL,
					rgt INT NOT NULL,
					INDEX IDX_62A97E9727ACA70 (parent_id),
					PRIMARY KEY(id)
				) ENGINE = InnoDB");
			App::getDb()->exec("
				CREATE TABLE article_ratings (
					id INT AUTO_INCREMENT NOT NULL,
					article_id INT DEFAULT NULL,
					person_id INT DEFAULT NULL,
					visitor_id INT DEFAULT NULL,
					rating INT NOT NULL,
					comment VARCHAR(2500) NOT NULL,
					date_created DATETIME NOT NULL,
					INDEX IDX_2364437E7294869C (article_id),
					INDEX IDX_2364437E217BBB47 (person_id),
					INDEX IDX_2364437E70BEE6D (visitor_id),
					PRIMARY KEY(id)
				) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
