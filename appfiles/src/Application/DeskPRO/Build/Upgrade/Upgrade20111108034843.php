<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111108034843 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, symbol VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;
INSERT INTO `currency` (`id`, `name`, `symbol`) VALUES
(1, 'USD', '$'),
(2, 'EU', '€');


CREATE TABLE deals (id INT AUTO_INCREMENT NOT NULL, deal_type_id INT DEFAULT NULL, deal_stage_id INT DEFAULT NULL, person_id INT DEFAULT NULL, assigned_agent_id INT DEFAULT NULL, currency_id INT DEFAULT NULL, status INT NOT NULL, probability DOUBLE PRECISION NOT NULL, deal_value DOUBLE PRECISION NOT NULL, visibility INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_EF39849B2156041B (deal_type_id), INDEX IDX_EF39849BC11FC009 (deal_stage_id), INDEX IDX_EF39849B217BBB47 (person_id), INDEX IDX_EF39849B49197702 (assigned_agent_id), INDEX IDX_EF39849B38248176 (currency_id), PRIMARY KEY(id)) ENGINE = InnoDB;

CREATE TABLE deal_peoples (deal_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_2D31D414F60E2305 (deal_id), INDEX IDX_2D31D414217BBB47 (person_id), PRIMARY KEY(deal_id, person_id)) ENGINE = InnoDB;

CREATE TABLE deal_organizations (deal_id INT NOT NULL, organization_id INT NOT NULL, INDEX IDX_A046BE1EF60E2305 (deal_id), INDEX IDX_A046BE1E32C8A3DE (organization_id), PRIMARY KEY(deal_id, organization_id)) ENGINE = InnoDB;

ALTER TABLE deals ADD FOREIGN KEY (deal_type_id) REFERENCES deals_type(id) ON DELETE SET NULL;

ALTER TABLE deals ADD FOREIGN KEY (deal_stage_id) REFERENCES deals_stage(id) ON DELETE SET NULL;

ALTER TABLE deals ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL;

ALTER TABLE deals ADD FOREIGN KEY (assigned_agent_id) REFERENCES people(id) ON DELETE SET NULL;

ALTER TABLE deals ADD FOREIGN KEY (currency_id) REFERENCES currency(id) ON DELETE SET NULL;

ALTER TABLE deal_peoples ADD FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE;

ALTER TABLE deal_peoples ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE;

ALTER TABLE deal_organizations ADD FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE;

ALTER TABLE deal_organizations ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE;


INSERT INTO `deals` (`id`, `deal_type_id`, `deal_stage_id`, `person_id`, `assigned_agent_id`, `currency_id`, `status`, `probability`, `deal_value`, `visibility`, `date_created`) VALUES
(1, 1, 1, 1, 1, 1, 0, 0.87, 123456, 0, '2011-11-06 21:11:52'),
(2, 2, 1, 1, 1, 2, 0, 0.45, 23121, 0, '2011-11-07 00:09:19'),
(3, 3, 2, 1, 1, 1, 0, 0.45, 23121, 0, '2011-11-07 00:09:19'),
(4, 1, 1, NULL, 1, 1, 0, 0.45, 23121, 0, '2011-11-07 18:57:47'),
(5, 1, 1, 1, 1, 1, 0, 0.87, 23121, 0, '2011-11-07 19:03:26'),
(6, 1, 1, 1, 1, 1, 1, 0.45, 23121, 0, '2011-11-07 19:20:38'),
(7, 1, 2, 1, 1, 2, 2, 0.45, 23121, 0, '2011-11-07 19:21:40'),
(11, 1, 1, NULL, NULL, 1, 2, 0.45, 23121, 0, '2011-11-07 19:25:14');






CREATE TABLE deals_stage (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, display_order INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;
INSERT INTO `deals_stage` (`id`, `name`, `display_order`) VALUES
(1, 'Proposal', 1),
(2, 'Bid', 2),
(3, 'Demo', 3),
(4, 'Contract', 4),
(5, 'Signoff', 5);

CREATE TABLE deals_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;

CREATE TABLE deal_type_stage (deal_type_id INT NOT NULL, deal_stage_id INT NOT NULL, INDEX IDX_2D6EB57B2156041B (deal_type_id), INDEX IDX_2D6EB57BC11FC009 (deal_stage_id), PRIMARY KEY(deal_type_id, deal_stage_id)) ENGINE = InnoDB;

ALTER TABLE deal_type_stage ADD FOREIGN KEY (deal_type_id) REFERENCES deals_type(id) ON DELETE CASCADE;

ALTER TABLE deal_type_stage ADD FOREIGN KEY (deal_stage_id) REFERENCES deals_stage(id) ON DELETE CASCADE;

INSERT INTO `deals_type` (`id`, `name`) VALUES
(1, 'Enterprise Sale '),
(2, 'PR Deal '),
(3, 'Upgrade Sale'),
(4, 'License Transfer');


CREATE TABLE IF NOT EXISTS `deal_type_stage` (
  `deal_type_id` int(11) NOT NULL,
  `deal_stage_id` int(11) NOT NULL,
  PRIMARY KEY (`deal_type_id`,`deal_stage_id`),
  KEY `IDX_2D6EB57B2156041B` (`deal_type_id`),
  KEY `IDX_2D6EB57BC11FC009` (`deal_stage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `deal_type_stage` (`deal_type_id`, `deal_stage_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 5),
(3, 2),
(3, 4),
(4, 5);


CREATE TABLE custom_data_deal (deal_type_id INT NOT NULL, field_id INT NOT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX IDX_128B7D522156041B (deal_type_id), INDEX IDX_128B7D52443707B0 (field_id), INDEX field_id_idx (field_id, deal_type_id), PRIMARY KEY(deal_type_id, field_id)) ENGINE = InnoDB;

ALTER TABLE custom_data_deal ADD FOREIGN KEY (deal_type_id) REFERENCES deals_type(id) ON DELETE CASCADE;

ALTER TABLE custom_data_deal ADD FOREIGN KEY (field_id) REFERENCES custom_def_deal(id) ON DELETE CASCADE;

CREATE TABLE custom_def_deal (id INT AUTO_INCREMENT NOT NULL, deal_type_id INT DEFAULT NULL, plugin_id VARCHAR(255) DEFAULT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL COMMENT '(DC2Type:array)', is_user_enabled TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, display_order INT NOT NULL, INDEX IDX_AB8B5F592156041B (deal_type_id), UNIQUE INDEX UNIQ_AB8B5F59EC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB;

ALTER TABLE custom_def_deal ADD FOREIGN KEY (deal_type_id) REFERENCES custom_def_deal(id) ON DELETE CASCADE;

ALTER TABLE custom_def_deal ADD FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE SET NULL;



CREATE TABLE deal_attachments (id INT AUTO_INCREMENT NOT NULL, deal_id INT DEFAULT NULL, person_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_9745F280F60E2305 (deal_id), INDEX IDX_9745F280217BBB47 (person_id), INDEX IDX_9745F280ED3E8EA5 (blob_id), PRIMARY KEY(id)) ENGINE = InnoDB;

ALTER TABLE deal_attachments ADD FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE;

ALTER TABLE deal_attachments ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL;

ALTER TABLE deal_attachments ADD FOREIGN KEY (blob_id) REFERENCES blobs(id) ON DELETE CASCADE;


CREATE TABLE deals_mapper (id INT AUTO_INCREMENT NOT NULL, dealid INT DEFAULT NULL, type VARCHAR(255) NOT NULL, typeid INT NOT NULL, INDEX IDX_8B45BCF64FC2C35F (dealid), PRIMARY KEY(id)) ENGINE = InnoDB;

ALTER TABLE deals_mapper ADD FOREIGN KEY (dealid) REFERENCES deals(id) ON DELETE CASCADE;


CREATE TABLE deal_notes (id INT AUTO_INCREMENT NOT NULL, deal_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, date_created DATETIME NOT NULL, note VARCHAR(255) NOT NULL, INDEX IDX_47C46590F60E2305 (deal_id), INDEX IDX_47C465903414710B (agent_id), PRIMARY KEY(id)) ENGINE = InnoDB;

ALTER TABLE deal_notes ADD FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE;

ALTER TABLE deal_notes ADD FOREIGN KEY (agent_id) REFERENCES people(id) ON DELETE SET NULL;


CREATE TABLE IF NOT EXISTS `deal_organizations` (
  `deal_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  PRIMARY KEY (`deal_id`,`organization_id`),
  KEY `IDX_A046BE1EF60E2305` (`deal_id`),
  KEY `IDX_A046BE1E32C8A3DE` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deal_organizations`
  ADD CONSTRAINT `deal_organizations_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_organizations_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;


CREATE TABLE IF NOT EXISTS `deal_peoples` (
  `deal_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`deal_id`,`person_id`),
  KEY `IDX_2D31D414F60E2305` (`deal_id`),
  KEY `IDX_2D31D414217BBB47` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deal_peoples`
  ADD CONSTRAINT `deal_peoples_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_peoples_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE;


CREATE TABLE IF NOT EXISTS `task_associations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) DEFAULT NULL,
  `person_id` int(11) DEFAULT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `deal_id` int(11) DEFAULT NULL,
  `discr` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_41B0E09C8DB60186` (`task_id`),
  KEY `IDX_41B0E09C217BBB47` (`person_id`),
  KEY `IDX_41B0E09C700047D2` (`ticket_id`),
  KEY `IDX_41B0E09C32C8A3DE` (`organization_id`),
  KEY `IDX_41B0E09CF60E2305` (`deal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `task_associations`
  ADD CONSTRAINT `task_associations_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_3` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_4` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_5` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE;
");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
