<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111216122525 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS sendmail_queue_part");
			App::getDb()->exec("DROP TABLE IF EXISTS sendmail_queue");

			App::getDb()->exec("CREATE TABLE sendmail_queue (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, to_address VARCHAR(255) NOT NULL, attempts INT NOT NULL, date_next_attempt DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, date_sent DATETIME DEFAULT NULL, has_sent TINYINT(1) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE sendmail_queue_part (id INT AUTO_INCREMENT NOT NULL, sendmail_queue_id INT DEFAULT NULL, data LONGTEXT NOT NULL, INDEX IDX_325FF8F4123DB51B (sendmail_queue_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE sendmail_queue_part ADD CONSTRAINT FK_325FF8F4123DB51B FOREIGN KEY (sendmail_queue_id) REFERENCES sendmail_queue(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
