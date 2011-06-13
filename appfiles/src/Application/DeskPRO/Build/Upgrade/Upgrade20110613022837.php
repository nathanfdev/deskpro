<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110613022837 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE  `chat_conversations` CHANGE  `date_first_agent_message`  `date_first_agent_message` DATETIME NULL DEFAULT NULL");
			App::getDb()->exec("
				CREATE TABLE chat_quick_replies (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, is_global TINYINT(1) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_30AB1C31217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB
			");
			App::getDb()->exec("
				INSERT INTO `chat_quick_replies` (`person_id`, `title`, `is_global`, `content`) VALUES
				(20001, 'Test 1', 1, 'Testing 1'),
				(20001, 'Test 2', 1, 'Testing 2'),
				(20001, 'Testing 3', 1, 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.'),
				(20001, 'Testing 4', 1, 'Pretty kittens everywhere')
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
