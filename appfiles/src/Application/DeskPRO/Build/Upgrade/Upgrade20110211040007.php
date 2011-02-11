<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110211040007 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Create tickets_search_active table');

		try {
			App::getDb()->exec("
				CREATE TABLE `tickets_search_active` (
				  `id` int(11) NOT NULL,
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
				  PRIMARY KEY (`id`),
				  KEY `status` (`status`),
				  KEY `person_id` (`person_id`) USING BTREE
				) ENGINE=MEMORY
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Create tickets_search_subject table');

		try {
			App::getDb()->exec("
				CREATE TABLE `tickets_search_subject` (
				  `ticket_id` int(11) NOT NULL,
				  `subject` varchar(1000) NOT NULL,
				  PRIMARY KEY (`ticket_id`)
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
		$this->output->writeln('Create tickets_search_message table');

		try {
			App::getDb()->exec("
				CREATE TABLE `tickets_search_message` (
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

	public function step4()
	{
		$this->output->writeln('Create triggers');

		try {
			App::getDb()->exec("
				CREATE TRIGGER ticket_search_insert AFTER INSERT ON tickets
				FOR EACH ROW
				BEGIN
					IF NEW.status = 'open' OR NEW.status = 'pending' THEN
						INSERT INTO tickets_search_active
						SET
							id                       = NEW.id,
							language_id              = NEW.language_id,
							category_id              = NEW.category_id,
							priority_id              = NEW.priority_id,
							workflow_id              = NEW.workflow_id,
							product_id               = NEW.product_id,
							person_id                = NEW.person_id,
							agent_id                 = NEW.agent_id,
							agent_team_id            = NEW.agent_team_id,
							organization_id          = NEW.organization_id,
							status                   = NEW.status,
							urgency                  = NEW.urgency,
							date_created             = NEW.date_created,
							date_first_agent_reply   = NEW.date_first_agent_reply,
							date_last_agent_reply    = NEW.date_last_agent_reply,
							date_last_user_reply     = NEW.date_last_user_reply,
							date_agent_waiting       = NEW.date_agent_waiting,
							date_user_waiting        = NEW.date_user_waiting,
							total_user_waiting       = NEW.total_user_waiting,
							total_to_first_reply     = NEW.total_to_first_reply;

						INSERT INTO tickets_search_subject
						SET
							ticket_id = NEW.id,
							subject   = NEW.subject;
					END IF;

					INSERT INTO tickets_search_message
						SET
							id                       = NEW.id,
							language_id              = NEW.language_id,
							category_id              = NEW.category_id,
							priority_id              = NEW.priority_id,
							workflow_id              = NEW.workflow_id,
							product_id               = NEW.product_id,
							person_id                = NEW.person_id,
							agent_id                 = NEW.agent_id,
							agent_team_id            = NEW.agent_team_id,
							organization_id          = NEW.organization_id,
							status                   = NEW.status,
							urgency                  = NEW.urgency,
							date_created             = NEW.date_created,
							date_first_agent_reply   = NEW.date_first_agent_reply,
							date_last_agent_reply    = NEW.date_last_agent_reply,
							date_last_user_reply     = NEW.date_last_user_reply,
							date_agent_waiting       = NEW.date_agent_waiting,
							date_user_waiting        = NEW.date_user_waiting,
							total_user_waiting       = NEW.total_user_waiting,
							total_to_first_reply     = NEW.total_to_first_reply,
							content                  = '';
				END
			");

			App::getDb()->exec("
				CREATE TRIGGER ticket_search_update AFTER UPDATE ON tickets
				FOR EACH ROW
				BEGIN
					IF NEW.status != 'open' AND NEW.status != 'pending' THEN
						DELETE FROM tickets_search_active WHERE id = NEW.id;
					ELSEIF OLD.status != 'open' AND OLD.status != 'pending' THEN
						INSERT INTO tickets_search_active
						SET
							id                       = NEW.id,
							language_id              = NEW.language_id,
							category_id              = NEW.category_id,
							priority_id              = NEW.priority_id,
							workflow_id              = NEW.workflow_id,
							product_id               = NEW.product_id,
							person_id                = NEW.person_id,
							agent_id                 = NEW.agent_id,
							agent_team_id            = NEW.agent_team_id,
							organization_id          = NEW.organization_id,
							status                   = NEW.status,
							urgency                  = NEW.urgency,
							date_created             = NEW.date_created,
							date_first_agent_reply   = NEW.date_first_agent_reply,
							date_last_agent_reply    = NEW.date_last_agent_reply,
							date_last_user_reply     = NEW.date_last_user_reply,
							date_agent_waiting       = NEW.date_agent_waiting,
							date_user_waiting        = NEW.date_user_waiting,
							total_user_waiting       = NEW.total_user_waiting,
							total_to_first_reply     = NEW.total_to_first_reply;
					ELSE
						UPDATE tickets_search_active
						SET
							id                       = NEW.id,
							language_id              = NEW.language_id,
							category_id              = NEW.category_id,
							priority_id              = NEW.priority_id,
							workflow_id              = NEW.workflow_id,
							product_id               = NEW.product_id,
							person_id                = NEW.person_id,
							agent_id                 = NEW.agent_id,
							agent_team_id            = NEW.agent_team_id,
							organization_id          = NEW.organization_id,
							status                   = NEW.status,
							urgency                  = NEW.urgency,
							date_created             = NEW.date_created,
							date_first_agent_reply   = NEW.date_first_agent_reply,
							date_last_agent_reply    = NEW.date_last_agent_reply,
							date_last_user_reply     = NEW.date_last_user_reply,
							date_agent_waiting       = NEW.date_agent_waiting,
							date_user_waiting        = NEW.date_user_waiting,
							total_user_waiting       = NEW.total_user_waiting,
							total_to_first_reply     = NEW.total_to_first_reply
						WHERE id = NEW.id;
					END IF;

					UPDATE tickets_search_message
					SET
						id                       = NEW.id,
						language_id              = NEW.language_id,
						category_id              = NEW.category_id,
						priority_id              = NEW.priority_id,
						workflow_id              = NEW.workflow_id,
						product_id               = NEW.product_id,
						person_id                = NEW.person_id,
						agent_id                 = NEW.agent_id,
						agent_team_id            = NEW.agent_team_id,
						organization_id          = NEW.organization_id,
						status                   = NEW.status,
						urgency                  = NEW.urgency,
						date_created             = NEW.date_created,
						date_first_agent_reply   = NEW.date_first_agent_reply,
						date_last_agent_reply    = NEW.date_last_agent_reply,
						date_last_user_reply     = NEW.date_last_user_reply,
						date_agent_waiting       = NEW.date_agent_waiting,
						date_user_waiting        = NEW.date_user_waiting,
						total_user_waiting       = NEW.total_user_waiting,
						total_to_first_reply     = NEW.total_to_first_reply
					WHERE id = NEW.id;

					IF NEW.subject != OLD.subject THEN
						UPDATE tickets_search_subject
						SET subject = NEW.subject
						WHERE id = NEW.id;
					END IF;
				END
			");

			App::getDb()->exec("
				CREATE TRIGGER ticket_search_delete AFTER DELETE ON tickets
				FOR EACH ROW
				BEGIN
					DELETE FROM tickets_search_active WHERE id = OLD.id;
					DELETE FROM tickets_search_message WHERE id = OLD.id;
					DELETE FROM tickets_search_subject WHERE id = OLD.id;
				END
			");

			App::getDb()->exec("
				CREATE TRIGGER ticket_search_message_insert AFTER INSERT ON tickets_messages
				FOR EACH ROW
				BEGIN
					DECLARE content LONGTEXT;
					SET content = (SELECT group_concat(message) FROM tickets_messages WHERE ticket_id = NEW.ticket_id);
					IF content IS NOT NULL THEN
						UPDATE tickets_search_message
						SET content = content
						WHERE ticket_id = NEW.ticket_id;
					END IF;
				END
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
