<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120117202152 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("ALTER TABLE `email_gateways` ADD `linked_transport_id` INT  NULL  DEFAULT NULL  AFTER `id`");
			App::getDb()->exec("ALTER TABLE email_gateways ADD CONSTRAINT FK_D0C6423237308465 FOREIGN KEY (linked_transport_id) REFERENCES email_transports(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE `email_gateways` DROP `default_gateway_address_id`");
			App::getDb()->exec("ALTER TABLE `email_gateway_addresses` ADD `run_order` INT  NOT NULL  DEFAULT '0'  AFTER `match_pattern`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
