<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110616154737 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				ALTER TABLE  `visitors` ADD  `name` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `last_page` ,
				ADD  `email` VARCHAR( 255 ) NOT NULL AFTER  `name`
			");
		} catch (\Exception $e) {}

		try {

			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");
			
			App::getDb()->exec("
				DROP TABLE IF EXISTS `article_comments` ,
				`comments_article` ,
				`ideas` ,
				`idea_categories` ,
				`idea_category_permissions` ,
				`idea_comments` ,
				`idea_status_categories` ,
				`idea_votes` ,
				`news_comments`
			");

			App::getDb()->exec("CREATE TABLE article_comments (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_A7662417294869C (article_id), INDEX IDX_A766241217BBB47 (person_id), INDEX IDX_A76624170BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE ideas (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, status_category INT DEFAULT NULL, category_id INT DEFAULT NULL, first_comment_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, status VARCHAR(15) NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, num_votes INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_1DB2F1DE217BBB47 (person_id), INDEX IDX_1DB2F1DE9B96BE19 (status_category), INDEX IDX_1DB2F1DE12469DE2 (category_id), UNIQUE INDEX UNIQ_1DB2F1DE69F11C14 (first_comment_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_E4FA1F8F727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_750A0C12D2112630 (usergroup_id), INDEX IDX_750A0C1212469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_comments (id INT AUTO_INCREMENT NOT NULL, idea_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_25B753935B6FEF7D (idea_id), INDEX IDX_25B75393217BBB47 (person_id), INDEX IDX_25B7539370BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_votes (id INT AUTO_INCREMENT NOT NULL, idea_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, num_votes INT NOT NULL, is_returned TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_104C491A5B6FEF7D (idea_id), INDEX IDX_104C491A217BBB47 (person_id), INDEX IDX_104C491A70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_status_categories (id INT AUTO_INCREMENT NOT NULL, status_type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE news_comments (id INT AUTO_INCREMENT NOT NULL, news_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_16A0357BB5A459A0 (news_id), INDEX IDX_16A0357B217BBB47 (person_id), INDEX IDX_16A0357B70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");

			// Some sample cats
			App::getDb()->exec("INSERT INTO `idea_categories` (`id`, `parent_id`, `title`, `display_order`, `root`, `depth`, `lft`, `rgt`) VALUES(1, NULL, 'Category 1', 0, 1, 0, 1, 2)");
			App::getDb()->exec("INSERT INTO `idea_categories` (`id`, `parent_id`, `title`, `display_order`, `root`, `depth`, `lft`, `rgt`) VALUES(2, NULL, 'Category 2', 0, 2, 0, 1, 2)");
			App::getDb()->exec("INSERT INTO `idea_categories` (`id`, `parent_id`, `title`, `display_order`, `root`, `depth`, `lft`, `rgt`) VALUES(3, NULL, 'Category 3', 0, 3, 0, 1, 2)");
			App::getDb()->exec("INSERT INTO `idea_categories` (`id`, `parent_id`, `title`, `display_order`, `root`, `depth`, `lft`, `rgt`) VALUES(4, NULL, 'Category 4', 0, 4, 0, 1, 2)");
			App::getDb()->exec("INSERT INTO `idea_categories` (`id`, `parent_id`, `title`, `display_order`, `root`, `depth`, `lft`, `rgt`) VALUES(5, NULL, 'Category 5', 0, 5, 0, 1, 2)");

			// Default status cats
			App::getDb()->exec("INSERT INTO `idea_status_categories` (`id`, `status_type`, `display_order`, `title`) VALUES(1, 'active', 0, 'Considering')");
			App::getDb()->exec("INSERT INTO `idea_status_categories` (`id`, `status_type`, `display_order`, `title`) VALUES(2, 'active', 1, 'Planning')");
			App::getDb()->exec("INSERT INTO `idea_status_categories` (`id`, `status_type`, `display_order`, `title`) VALUES(3, 'active', 2, 'Started')");
			App::getDb()->exec("INSERT INTO `idea_status_categories` (`id`, `status_type`, `display_order`, `title`) VALUES(4, 'closed', 0, 'Completed')");
			App::getDb()->exec("INSERT INTO `idea_status_categories` (`id`, `status_type`, `display_order`, `title`) VALUES(5, 'closed', 1, 'Duplicate')");
			App::getDb()->exec("INSERT INTO `idea_status_categories` (`id`, `status_type`, `display_order`, `title`) VALUES(6, 'closed', 2, 'Declined')");
			App::getDb()->exec("INSERT INTO `idea_status_categories` (`id`, `status_type`, `display_order`, `title`) VALUES(7, 'closed', 3, 'Already Exists')");

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
