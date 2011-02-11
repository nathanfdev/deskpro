<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110211154215 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create people_emails table');

		try {
			App::getDb()->exec("
				ALTER TABLE  `people_emails` ADD  `email_domain` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `email` ,
				ADD INDEX (`email_domain`)
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Add second message table');

		try {
			App::getDb()->exec("
				CREATE TABLE `tickets_search_message_active` (
				  `ticket_id` int(11) NOT NULL,
				  `language_id` int(11) DEFAULT NULL,
				  `department_id` int(11) DEFAULT NULL,
				  `category_id` int(11) DEFAULT NULL,
				  `priority_id` int(11) DEFAULT NULL,
				  `workflow_id` int(11) DEFAULT NULL,
				  `product_id` int(11) DEFAULT NULL,
				  `person_id` int(11) NOT NULL,
				  `agent_id` int(11) DEFAULT NULL,
				  `agent_team_id` int(11) DEFAULT NULL,
				  `organization_id` int(11) DEFAULT NULL,
				  `status` char(10) NOT NULL,
				  `urgency` int(11) NOT NULL,
				  `date_created` datetime NOT NULL,
				  `date_first_agent_reply` datetime DEFAULT NULL,
				  `date_last_agent_reply` datetime DEFAULT NULL,
				  `date_last_user_reply` datetime DEFAULT NULL,
				  `date_agent_waiting` datetime DEFAULT NULL,
				  `date_user_waiting` datetime DEFAULT NULL,
				  `total_user_waiting` int(11) NOT NULL,
				  `total_to_first_reply` int(11) NOT NULL,
				  `content` longtext NOT NULL,
				  PRIMARY KEY (`ticket_id`),
				  KEY `status` (`status`),
				  KEY `person_id` (`person_id`) USING BTREE,
				  FULLTEXT KEY `content` (`content`)
				) ENGINE=MyISAM
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step3()
	{
		$this->output->writeln('Recreate triggers');

		try {
			App::getDb()->exec('DROP TRIGGER IF EXISTS ticket_search_insert');
			App::getDb()->exec('DROP TRIGGER IF EXISTS ticket_search_update');
			App::getDb()->exec('DROP TRIGGER IF EXISTS ticket_search_delete');
			App::getDb()->exec('DROP TRIGGER IF EXISTS ticket_search_message_insert');

			$data_reader = new \Application\DeskPRO\Install\InstallData('triggers.sql');
			foreach ($data_reader as $k => $sql) {
				$this->output->writeln("Trigger ... $k");
				App::getDb()->exec($sql);
			}
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
