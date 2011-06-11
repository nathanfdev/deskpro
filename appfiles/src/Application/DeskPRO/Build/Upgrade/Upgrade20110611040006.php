<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110611040006 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS chat_conversations");
			App::getDb()->exec("DROP TABLE IF EXISTS chat_messages");
			App::getDb()->exec("CREATE TABLE chat_conversations (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, person_id INT DEFAULT NULL, session_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, status VARCHAR(15) NOT NULL, person_name VARCHAR(255) NOT NULL, person_email VARCHAR(255) NOT NULL, is_agent TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, date_assigned DATETIME DEFAULT NULL, date_first_agent_message DATETIME NOT NULL, date_ended DATETIME DEFAULT NULL, INDEX IDX_5813432EAE80F5DF (department_id), INDEX IDX_5813432E3414710B (agent_id), INDEX IDX_5813432E217BBB47 (person_id), INDEX IDX_5813432E613FECDF (session_id), INDEX IDX_5813432E70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE chat_messages (id INT AUTO_INCREMENT NOT NULL, conversation_id INT DEFAULT NULL, author_id INT DEFAULT NULL, person_name VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, is_sys TINYINT(1) NOT NULL, is_user_hidden TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_EF20C9A69AC0396 (conversation_id), INDEX IDX_EF20C9A6F675F31B (author_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
