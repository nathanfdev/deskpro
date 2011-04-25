<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110425163701 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add label tables');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS labels_articles");
			App::getDb()->exec("DROP TABLE IF EXISTS labels_downloads");
			App::getDb()->exec("DROP TABLE IF EXISTS labels_ideas");
			App::getDb()->exec("DROP TABLE IF EXISTS labels_news");
			App::getDb()->exec("CREATE TABLE labels_articles (article_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_2F30AF707294869C (article_id), PRIMARY KEY(article_id, label)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE labels_downloads (download_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_588FD17DC667AEAB (download_id), PRIMARY KEY(download_id, label)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE labels_ideas (idea_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_78BD7D1B5B6FEF7D (idea_id), PRIMARY KEY(idea_id, label)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE labels_news (news_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_A2869A08B5A459A0 (news_id), PRIMARY KEY(news_id, label)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
