<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111024134257 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			//App::getDb()->exec("DROP TABLE IF EXISTS `chat_quick_replies`");
			//App::getDb()->exec("CREATE TABLE text_snippets (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, snippet LONGTEXT NOT NULL, INDEX IDX_5B6379CE217BBB47 (person_id), INDEX IDX_5B6379CE12469DE2 (category_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			//App::getDb()->exec("ALTER TABLE text_snippets ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			//App::getDb()->exec("ALTER TABLE text_snippets ADD FOREIGN KEY (category_id) REFERENCES ticket_snippet_categories(id) ON DELETE SET NULL");
			//App::getDb()->exec("CREATE TABLE text_snippet_categories (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, typename VARCHAR(30) NOT NULL, is_global TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_F3B50AF1217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE text_snippetcat_to_team (team_id INT NOT NULL, INDEX IDX_4E1AB897296CD8AE (team_id), PRIMARY KEY(team_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE text_snippet_categories ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE text_snippetcat_to_team ADD FOREIGN KEY (team_id) REFERENCES agent_teams(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
