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
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS `currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `symbol` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `custom_data_deal` (
  `deal_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `input` longtext NOT NULL,
  PRIMARY KEY (`deal_id`,`field_id`),
  KEY `IDX_128B7D52F60E2305` (`deal_id`),
  KEY `IDX_128B7D52443707B0` (`field_id`),
  KEY `field_id_idx` (`field_id`,`deal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `custom_data_deal`
  ADD CONSTRAINT `custom_data_deal_ibfk_4` FOREIGN KEY (`field_id`) REFERENCES `custom_def_deal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `custom_data_deal_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `custom_data_deal_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `custom_def_deal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `custom_data_deal_ibfk_3` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE;


CREATE TABLE IF NOT EXISTS `custom_def_deal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `plugin_id` varchar(255) DEFAULT NULL,
  `js_class` varchar(255) NOT NULL,
  `has_form_template` tinyint(1) NOT NULL,
  `has_display_template` tinyint(1) NOT NULL,
  `title` varchar(255) NOT NULL,
  `handler_class` varchar(255) DEFAULT NULL,
  `options` longtext NOT NULL COMMENT '(DC2Type:array)',
  `is_user_enabled` tinyint(1) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `display_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AB8B5F59EC942BCF` (`plugin_id`),
  KEY `IDX_AB8B5F59727ACA70` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


ALTER TABLE `custom_def_deal`
  ADD CONSTRAINT `custom_def_deal_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `custom_def_deal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `custom_def_deal_ibfk_2` FOREIGN KEY (`plugin_id`) REFERENCES `plugins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `custom_def_deal_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `custom_def_deal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `custom_def_deal_ibfk_4` FOREIGN KEY (`plugin_id`) REFERENCES `plugins` (`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `deals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_type_id` int(11) DEFAULT NULL,
  `deal_stage_id` int(11) DEFAULT NULL,
  `person_id` int(11) DEFAULT NULL,
  `assigned_agent_id` int(11) DEFAULT NULL,
  `currency_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `probability` double NOT NULL,
  `deal_value` double NOT NULL,
  `visibility` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EF39849B2156041B` (`deal_type_id`),
  KEY `IDX_EF39849BC11FC009` (`deal_stage_id`),
  KEY `IDX_EF39849B217BBB47` (`person_id`),
  KEY `IDX_EF39849B49197702` (`assigned_agent_id`),
  KEY `IDX_EF39849B38248176` (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `deals`
  ADD CONSTRAINT `deals_ibfk_10` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_1` FOREIGN KEY (`deal_type_id`) REFERENCES `deals_type` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_2` FOREIGN KEY (`deal_stage_id`) REFERENCES `deals_stage` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_3` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_4` FOREIGN KEY (`assigned_agent_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_5` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_6` FOREIGN KEY (`deal_type_id`) REFERENCES `deals_type` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_7` FOREIGN KEY (`deal_stage_id`) REFERENCES `deals_stage` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_8` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_ibfk_9` FOREIGN KEY (`assigned_agent_id`) REFERENCES `people` (`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `deals_mapper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dealid` int(11) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `typeid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8B45BCF64FC2C35F` (`dealid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `deals_mapper`
  ADD CONSTRAINT `deals_mapper_ibfk_2` FOREIGN KEY (`dealid`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_mapper_ibfk_1` FOREIGN KEY (`dealid`) REFERENCES `deals` (`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `deals_stage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `deals_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `deals_type_def` (
  `deal_type_id` int(11) NOT NULL,
  `deal_def_id` int(11) NOT NULL,
  PRIMARY KEY (`deal_type_id`,`deal_def_id`),
  KEY `IDX_654B5CF52156041B` (`deal_type_id`),
  KEY `IDX_654B5CF5E07F23A3` (`deal_def_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deals_type_def`
  ADD CONSTRAINT `deals_type_def_ibfk_4` FOREIGN KEY (`deal_def_id`) REFERENCES `custom_def_deal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_type_def_ibfk_1` FOREIGN KEY (`deal_type_id`) REFERENCES `deals_type` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_type_def_ibfk_2` FOREIGN KEY (`deal_def_id`) REFERENCES `custom_def_deal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_type_def_ibfk_3` FOREIGN KEY (`deal_type_id`) REFERENCES `deals_type` (`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `deal_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) DEFAULT NULL,
  `person_id` int(11) DEFAULT NULL,
  `blob_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9745F280F60E2305` (`deal_id`),
  KEY `IDX_9745F280217BBB47` (`person_id`),
  KEY `IDX_9745F280ED3E8EA5` (`blob_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `deal_attachments`
  ADD CONSTRAINT `deal_attachments_ibfk_6` FOREIGN KEY (`blob_id`) REFERENCES `blobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_attachments_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_attachments_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deal_attachments_ibfk_3` FOREIGN KEY (`blob_id`) REFERENCES `blobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_attachments_ibfk_4` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_attachments_ibfk_5` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `deal_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `note` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_47C46590F60E2305` (`deal_id`),
  KEY `IDX_47C465903414710B` (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `deal_notes`
  ADD CONSTRAINT `deal_notes_ibfk_4` FOREIGN KEY (`agent_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deal_notes_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_notes_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deal_notes_ibfk_3` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `deal_organizations` (
  `deal_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  PRIMARY KEY (`deal_id`,`organization_id`),
  KEY `IDX_A046BE1EF60E2305` (`deal_id`),
  KEY `IDX_A046BE1E32C8A3DE` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `labels_deals` (
  `deal_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`deal_id`,`label`),
  KEY `IDX_8A36085EF60E2305` (`deal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `labels_deals`
  ADD CONSTRAINT `labels_deals_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `labels_deals_ibfk_2` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE;

ALTER TABLE `deal_organizations`
  ADD CONSTRAINT `deal_organizations_ibfk_4` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_organizations_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_organizations_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_organizations_ibfk_3` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `deal_people` (
  `deal_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`deal_id`,`person_id`),
  KEY `IDX_3C51E9AAF60E2305` (`deal_id`),
  KEY `IDX_3C51E9AA217BBB47` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deal_people`
  ADD CONSTRAINT `deal_people_ibfk_4` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_people_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_people_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_people_ibfk_3` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `deal_type_stage` (
  `id` int(11) NOT NULL,
  `deal_type_id` int(11) NOT NULL,
  `deal_stage_id` int(11) NOT NULL,
  `display_order` int(11) NOT NULL,
  PRIMARY KEY (`id`,`deal_type_id`,`deal_stage_id`),
  KEY `IDX_2D6EB57B2156041B` (`deal_type_id`),
  KEY `IDX_2D6EB57BC11FC009` (`deal_stage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deal_type_stage`
  ADD CONSTRAINT `deal_type_stage_ibfk_4` FOREIGN KEY (`deal_stage_id`) REFERENCES `deals_stage` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_type_stage_ibfk_1` FOREIGN KEY (`deal_type_id`) REFERENCES `deals_type` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_type_stage_ibfk_2` FOREIGN KEY (`deal_stage_id`) REFERENCES `deals_stage` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_type_stage_ibfk_3` FOREIGN KEY (`deal_type_id`) REFERENCES `deals_type` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `task_associations_ibfk_10` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_3` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_4` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_5` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_6` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_7` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_8` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_associations_ibfk_9` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

INSERT INTO `currency` (`id`, `name`, `symbol`) VALUES
(1, 'USD', '$'),
(2, 'EU', '€');

INSERT INTO `deals_stage` (`id`, `name`) VALUES
(1, 'Proposal', 1),
(2, 'Bid', 2),
(3, 'Demo', 3),
(4, 'Contract', 4),
(5, 'Signoff', 5);

INSERT INTO `deals_type` (`id`, `name`) VALUES
(1, 'Enterprise Sale '),
(2, 'PR Deal '),
(3, 'Upgrade Sale'),
(4, 'License Transfer');

INSERT INTO `deal_type_stage` (`deal_type_id`, `deal_stage_id`, `display_order`) VALUES
(1, 1,1),
(1, 2,2),
(2, 1, 1),
(2, 5, 2),
(3, 2, 2),
(3, 4, 1),
(4, 5, 1);


INSERT INTO `deals` (`id`, `deal_type_id`, `deal_stage_id`, `person_id`, `assigned_agent_id`, `currency_id`, `status`, `probability`, `deal_value`, `visibility`, `date_created`) VALUES
(1, 1, 1, 1, 1, 1, 0, 0.87, 123456, 0, '2011-11-06 21:11:52'),
(2, 2, 1, 1, 1, 2, 0, 0.45, 23121, 0, '2011-11-07 00:09:19'),
(3, 3, 2, 1, 1, 1, 0, 0.45, 23121, 0, '2011-11-07 00:09:19'),
(4, 1, 1, NULL, 1, 1, 0, 0.45, 23121, 0, '2011-11-07 18:57:47'),
(5, 1, 1, 1, 1, 1, 0, 0.87, 23121, 0, '2011-11-07 19:03:26'),
(6, 1, 1, 1, 1, 1, 1, 0.45, 23121, 0, '2011-11-07 19:20:38'),
(7, 1, 2, 1, 1, 2, 2, 0.45, 23121, 0, '2011-11-07 19:21:40'),
(11, 1, 1, NULL, NULL, 1, 2, 0.45, 23121, 0, '2011-11-07 19:25:14');
");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
