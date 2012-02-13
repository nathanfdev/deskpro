<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111219083808 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE email_gateway_addresses (id INT AUTO_INCREMENT NOT NULL, email_gateway_id INT DEFAULT NULL, match_type VARCHAR(15) NOT NULL, match_pattern VARCHAR(255) NOT NULL, INDEX IDX_EC270D12FBCC7CDF (email_gateway_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE email_gateway_addresses ADD CONSTRAINT FK_EC270D12FBCC7CDF FOREIGN KEY (email_gateway_id) REFERENCES email_gateways(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE `email_gateways` ADD `default_gateway_address_id` INT  NULL  DEFAULT NULL  AFTER `id`");
			App::getDb()->exec("ALTER TABLE email_gateways ADD CONSTRAINT FK_D0C642329749D4F4 FOREIGN KEY (default_gateway_address_id) REFERENCES email_gateway_addresses(id) ON DELETE CASCADE");
			App::getDb()->exec("DROP TABLE `email_gateway_logs`");
			App::getDb()->exec("ALTER TABLE `tickets` ADD `email_gateway_address_id` INT  NULL  DEFAULT NULL  AFTER `email_gateway_id`");
			App::getDb()->exec("ALTER TABLE tickets ADD CONSTRAINT FK_54469DF4F2598614 FOREIGN KEY (email_gateway_address_id) REFERENCES email_gateway_addresses(id) ON DELETE SET NULL");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
