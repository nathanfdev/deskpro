<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110603182455 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("
				DROP TABLE IF EXISTS  `chat_conversations` ,
				`chat_conversation_to_person` ,
				`chat_messages`
			");

			App::getDb()->exec("CREATE TABLE chat_conversations (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, is_agent TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE chat_conversation_to_person (conversation_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_1CA5AE439AC0396 (conversation_id), INDEX IDX_1CA5AE43217BBB47 (person_id), PRIMARY KEY(conversation_id, person_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE chat_messages (id INT AUTO_INCREMENT NOT NULL, conversation_id INT DEFAULT NULL, author_id INT DEFAULT NULL, content LONGTEXT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_EF20C9A69AC0396 (conversation_id), INDEX IDX_EF20C9A6F675F31B (author_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
