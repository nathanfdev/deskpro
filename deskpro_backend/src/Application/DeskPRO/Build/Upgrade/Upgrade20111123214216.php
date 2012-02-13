<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111123214216 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS department_permissions");
			App::getDb()->exec("CREATE TABLE department_permissions (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, person_id INT DEFAULT NULL, app VARCHAR(50) NOT NULL, INDEX IDX_84C36B30AE80F5DF (department_id), INDEX IDX_84C36B30217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE department_permissions ADD CONSTRAINT FK_84C36B30AE80F5DF FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE department_permissions ADD CONSTRAINT FK_84C36B30217BBB47 FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
