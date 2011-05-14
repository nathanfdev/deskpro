<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110514063431 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('API token tables');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `api_auth_codes`");
			App::getDb()->exec("DROP TABLE IF EXISTS `api_auth_tokens`");
			App::getDb()->exec("DROP TABLE IF EXISTS `api_keys`");
			App::getDb()->exec("CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) DEFAULT NULL,
  `code` varchar(25) NOT NULL,
  `note` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `api_keys_person_id_idx` (`person_id`)
) ENGINE=InnoDB");
			App::getDb()->exec("CREATE TABLE `api_auth_tokens` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `person_id` int(11) DEFAULT NULL,
			  `token` varchar(50) NOT NULL,
			  `scope` varchar(250) DEFAULT NULL,
			  `date_created` datetime NOT NULL,
			  `date_expires` datetime NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `api_auth_tokens_person_id_idx` (`person_id`)
			) ENGINE=InnoDB ");
			App::getDb()->exec("CREATE TABLE `api_auth_codes` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `person_id` int(11) DEFAULT NULL,
			  `code` varchar(50) NOT NULL,
			  `scope` varchar(250) DEFAULT NULL,
			  `redirect_url` varchar(250) DEFAULT NULL,
			  `date_created` datetime NOT NULL,
			  `date_expires` datetime NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `api_auth_codes_person_id_idx` (`person_id`)
			) ENGINE=InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
