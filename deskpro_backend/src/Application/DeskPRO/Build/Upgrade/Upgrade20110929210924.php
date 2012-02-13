<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110929210924 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE organization_email_domains (domain VARCHAR(255) NOT NULL, organization_id INT DEFAULT NULL, INDEX IDX_2CCB20C232C8A3DE (organization_id), PRIMARY KEY(domain)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE organization_email_domains ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
