<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120220182825 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE user_rules (id INT AUTO_INCREMENT NOT NULL, email_patterns LONGTEXT NOT NULL COMMENT '(DC2Type:array)', run_order INT NOT NULL, add_organization_id INT DEFAULT NULL, add_usergroup_id INT DEFAULT NULL, INDEX IDX_6B5862642940B3FB (add_organization_id), INDEX IDX_6B586264A19F75EA (add_usergroup_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
