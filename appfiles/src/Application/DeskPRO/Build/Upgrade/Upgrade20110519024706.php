<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110519024706 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreate product, add articles');

		try {
			App::getDb()->exec("SET FOREIGN_KEY_CHECKS=0");
			App::getDb()->exec("DROP TABLE IF EXISTS products");
			App::getDb()->exec("CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_B3BA5A5A727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("DROP TABLE IF EXISTS articles");
			App::getDb()->exec("CREATE TABLE articles (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, language_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, excerpt VARCHAR(1000) NOT NULL, content LONGTEXT NOT NULL, view_count INT NOT NULL, total_rating INT NOT NULL, num_ratings INT NOT NULL, status VARCHAR(15) NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, display_order INT NOT NULL, date_created DATETIME NOT NULL, date_end DATETIME DEFAULT NULL, end_action VARCHAR(10) DEFAULT NULL, INDEX IDX_BFDD3168217BBB47 (person_id), INDEX IDX_BFDD316882F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}