<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110302045734 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Alter email gateways');

		try {
			App::getDb()->exec("ALTER TABLE  `email_gateways` ADD  `address` VARCHAR( 255 ) NOT NULL AFTER  `name`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Recreate sources table');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `email_sources_blobs`");
			App::getDb()->exec("DROP TABLE IF EXISTS `email_sources`");
			App::getDb()->exec("CREATE TABLE email_sources (id INT AUTO_INCREMENT NOT NULL, gateway_id INT NOT NULL, object_type VARCHAR(50) NOT NULL, object_id INT NOT NULL, headers VARCHAR(1000) NOT NULL, status VARCHAR(15) NOT NULL, save_path VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, INDEX email_sources_gateway_id_idx (gateway_id), INDEX object_idx (object_type, object_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE email_sources_blobs (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, data LONGTEXT NOT NULL, INDEX email_sources_blobs_source_id_idx (source_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
