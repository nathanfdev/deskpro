<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110607034423 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `chat_conversations`");
			App::getDb()->exec("
				CREATE TABLE chat_conversations(
					id INT AUTO_INCREMENT NOT NULL ,
					agent_id INT DEFAULT NULL ,
					person_id INT DEFAULT NULL ,
					visitor_id INT DEFAULT NULL ,
					subject VARCHAR( 255 ) NOT NULL ,
					is_agent TINYINT( 1 ) NOT NULL ,
					date_created DATETIME NOT NULL ,
					date_assigned DATETIME DEFAULT NULL ,
					date_ended DATETIME DEFAULT NULL ,
					INDEX IDX_5813432E3414710B( agent_id ) ,
					INDEX IDX_5813432E217BBB47( person_id ) ,
					INDEX IDX_5813432E70BEE6D( visitor_id ) ,
					PRIMARY KEY ( id )
				) ENGINE = INNODB
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
