CREATE TABLE `attachments` (
  `id` int(11) unsigned NOT NULL,
  `object_type` varchar(150) NOT NULL COMMENT 'The object this is attached to',
  `object_id` int(10) unsigned NOT NULL COMMENT 'The ID of the object',
  `save_path` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'The path to the data if stored on the file system',
  `person_id` int(11) unsigned NOT NULL COMMENT 'Who uploaded the attachment',
  `date_created` int(11) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filesize` int(11) unsigned NOT NULL,
  `content_type` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`),
  KEY `object` (`object_type`,`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `attachments_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `attachment_id` int(11) unsigned NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attachment_id` (`attachment_id`),
  KEY `attachment_id_2` (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `custom_data_people` (
  `field_id` int(11) unsigned NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  `value` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'An integer value. Eg. a timestamp',
  `input` text NOT NULL COMMENT 'A free string value',
  PRIMARY KEY (`field_id`,`person_id`),
  KEY `value` (`field_id`,`value`),
  KEY `ticket_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `custom_data_tickets` (
  `field_id` int(11) unsigned NOT NULL,
  `ticket_id` int(11) unsigned NOT NULL,
  `value` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'An integer value. Eg. a timestamp',
  `input` text NOT NULL COMMENT 'A free string value',
  PRIMARY KEY (`ticket_id`,`field_id`),
  KEY `value` (`field_id`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `custom_def_people` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The parent definition if this is a subfield',
  `title` varchar(255) NOT NULL,
  `handler_class` varchar(150) NOT NULL COMMENT 'PHP handler class',
  `options` mediumblob NOT NULL COMMENT 'Serialized PHP array of options to give to the handler',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `custom_def_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The parent definition if this is a subfield',
  `title` varchar(255) NOT NULL,
  `handler_class` varchar(150) NOT NULL COMMENT 'PHP handler class',
  `options` mediumblob NOT NULL COMMENT 'Serialized PHP array of options to give to the handler',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `email_gateways` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'Simple display title for the gateway',
  `connection_handler` varchar(150) NOT NULL COMMENT 'The PHP class that handles connecting and working with the server',
  `connection_options` mediumblob NOT NULL COMMENT 'Options for the connection, like username etc',
  `processor_handler` varchar(155) NOT NULL COMMENT 'The PHP class that handles processing messages',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `date_last_checked` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The last time the mailbox was checked',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `email_gateway_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gateway_id` int(11) unsigned NOT NULL,
  `date_created` int(11) unsigned NOT NULL,
  `message` text NOT NULL COMMENT 'Log info',
  `data` mediumblob NOT NULL COMMENT 'Serialized PHP array of additional data',
  `log_type` varchar(50) NOT NULL COMMENT 'The type of log: debug, info, error',
  PRIMARY KEY (`id`),
  KEY `gateway_id` (`gateway_id`),
  KEY `date_created` (`date_created`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `email_sources` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gateway_id` int(11) unsigned NOT NULL,
  `date_created` int(11) unsigned NOT NULL,
  `object_type` varchar(150) NOT NULL COMMENT 'The type of thing this email is for eg ticket',
  `object_id` int(11) unsigned NOT NULL COMMENT 'The ID of the object',
  `headers` mediumblob NOT NULL COMMENT 'Just the email headers',
  `status` enum('inserted','decoded','complete') NOT NULL,
  `save_path` varchar(255) NOT NULL COMMENT 'The path the raw email was saved to if in the filesystem',
  PRIMARY KEY (`id`),
  KEY `gateway_id` (`gateway_id`),
  KEY `date_created` (`date_created`),
  KEY `object` (`object_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `email_sources_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int(11) unsigned NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `source_id` (`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `error_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date_created` int(11) unsigned NOT NULL,
  `log_type` varchar(50) NOT NULL,
  `summary` text NOT NULL,
  `backtrace` text NOT NULL,
  `details` mediumblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_created` (`date_created`),
  KEY `log_type` (`log_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `labels_definitions` (
  `label` varchar(255) NOT NULL,
  `object` varchar(80) NOT NULL,
  PRIMARY KEY (`label`,`object`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `labels_organizations` (
  `label` varchar(255) NOT NULL,
  `organization_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`label`,`organization_id`),
  KEY `organization_id` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `labels_person` (
  `label` varchar(255) NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`label`,`person_id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `labels_tickets` (
  `label` varchar(255) NOT NULL,
  `ticket_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`label`,`ticket_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `languages` (
  `id` int(11) unsigned NOT NULL,
  `parent_id` int(11) unsigned NOT NULL,
  `locale` varchar(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `organizations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'The name of the company',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `organization_contact_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned NOT NULL,
  `data_type` varchar(150) NOT NULL COMMENT 'The contact data type: address, phone etc',
  `field_1` text NOT NULL,
  `field_2` text NOT NULL,
  `field_3` text NOT NULL,
  `field_4` text NOT NULL,
  `field_5` text NOT NULL,
  `field_6` text NOT NULL,
  `field_7` text NOT NULL,
  `field_8` text NOT NULL,
  `field_9` text NOT NULL,
  `field_10` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `organization_id` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `people` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The users language',
  `style_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'If this is a user, their chosen style',
  `organization_id` int(11) unsigned NOT NULL DEFAULT '0',
  `primary_email_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The users primary email address',
  `is_user` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If this is a user. Eg they can log in.',
  `is_contact` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If this is a contact',
  `is_agent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If this is an agent',
  `created_at` int(11) unsigned NOT NULL COMMENT 'When the record was first created',
  `last_login_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'When the user last logged in',
  `timezone` varchar(50) NOT NULL DEFAULT 'UTC' COMMENT 'A timezone name. Eg Europe/London',
  `password` char(40) NOT NULL COMMENT 'The users hashed passowrd',
  `salt` char(15) NOT NULL COMMENT 'The users salt',
  `first_name` varchar(255) NOT NULL COMMENT 'The persons first/given name',
  `last_name` varchar(255) NOT NULL COMMENT 'The persons last/family name',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `people_contact_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int(10) unsigned NOT NULL,
  `data_type` varchar(150) NOT NULL COMMENT 'The contact data type: address, phone etc',
  `field_1` text NOT NULL,
  `field_2` text NOT NULL,
  `field_3` text NOT NULL,
  `field_4` text NOT NULL,
  `field_5` text NOT NULL,
  `field_6` text NOT NULL,
  `field_7` text NOT NULL,
  `field_8` text NOT NULL,
  `field_9` text NOT NULL,
  `field_10` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `people_notes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int(11) unsigned NOT NULL,
  `agent_id` int(11) unsigned NOT NULL COMMENT 'Agent that wrote the note',
  `date_created` int(11) unsigned NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `people_search` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The users language',
  `primary_email_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The users primary email address',
  `is_user` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If this is a user. Eg they can log in.',
  `is_contact` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If this is a contact',
  `is_agent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If this is an agent',
  `first_name` varchar(255) NOT NULL COMMENT 'The persons first/given name',
  `last_name` varchar(255) NOT NULL COMMENT 'The persons last/family name',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `people_search_data` (
  `field_id` int(11) unsigned NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  `value` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'An integer value. Eg. a timestamp',
  `input` text NOT NULL COMMENT 'A free string value',
  PRIMARY KEY (`field_id`,`person_id`),
  KEY `value` (`field_id`,`value`),
  KEY `ticket_id` (`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `people_search_emails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int(11) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `person_emails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int(11) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `is_validated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is this field validated',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auth` char(8) NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  `date_createad` int(11) unsigned NOT NULL,
  `date_last_page` int(11) unsigned NOT NULL COMMENT 'The last time the user actually loaded a page',
  `date_last_update` int(11) unsigned NOT NULL COMMENT 'The last time the session was updated eg includes polling',
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `style` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tickets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ticket ID',
  `department_id` int(11) unsigned NOT NULL COMMENT 'Department ID',
  `category_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Category ID',
  `priority_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Priority ID',
  `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Product ID',
  `person_id` int(11) unsigned NOT NULL COMMENT 'The primary end-user this ticket belongs to',
  `agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The primary agent this ticket belongs to',
  `creation_system` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'The system that created this ticket: web_user, web_agent, gateway, api, split',
  `company_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The company this ticket is associated with',
  `status` enum('awaiting_agent','awaiting_user','pending','resolved','hidden','closed') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'awaiting_agent' COMMENT 'The status of the ticket',
  `hidden_status` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `date_created` int(11) unsigned NOT NULL COMMENT 'Timestamp when this ticket was created',
  `date_resolved` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'When the ticket was resolved',
  `date_closed` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'When the ticket was closed',
  `date_first_agent_reply` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The time of the first agents reply',
  `date_last_agent_reply` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The time of the last agents reply',
  `date_last_user_reply` int(11) unsigned NOT NULL COMMENT 'The time of the last user reply',
  `date_agent_waiting` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The time when an agent first started waiting on a user',
  `date_user_waiting` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The time when a user first started waiting for a response',
  `total_user_waiting` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'How long the user has waited total',
  `locked_by` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Who locked the ticket',
  `date_locked` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'When the ticket was locked',
  `has_attachments` tinyint(1) NOT NULL DEFAULT '0',
  `has_flagged` tinyint(1) NOT NULL DEFAULT '0',
  `has_participants` tinyint(1) NOT NULL DEFAULT '0',
  `subject` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_attachments` (
  `ticket_id` int(11) unsigned NOT NULL,
  `attachment_id` int(11) unsigned NOT NULL,
  `message_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'If the attachment is on a specific message',
  PRIMARY KEY (`ticket_id`,`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_deleted` (
  `ticket_id` int(11) unsigned NOT NULL COMMENT 'The old ticket id',
  `new_ticket_id` int(11) unsigned NOT NULL COMMENT 'The new ticket id if it was merged',
  `person_id` int(11) unsigned NOT NULL COMMENT 'Who made the change',
  `date_created` int(11) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_flagged` (
  `ticket_id` int(11) unsigned NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`ticket_id`,`person_id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_log` (
  `id` int(11) unsigned NOT NULL,
  `ticket_id` int(11) unsigned NOT NULL,
  `person_id` int(11) unsigned NOT NULL COMMENT 'The person that made the change',
  `date_created` int(11) unsigned NOT NULL,
  `action_type` varchar(255) NOT NULL COMMENT 'What this is logging about',
  `summary` varchar(255) NOT NULL COMMENT 'A summary of what happened',
  `details` mediumblob NOT NULL COMMENT 'More data such as IDs',
  `is_undoable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) unsigned NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  `date_created` int(11) unsigned NOT NULL,
  `is_agent_note` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If this is a hidden note on a ticket',
  `message_hash` char(40) NOT NULL COMMENT 'The hash of this message used in finding dupes',
  `message` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_participants` (
  `ticket_id` int(11) unsigned NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`ticket_id`,`person_id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_search` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ticket ID',
  `department_id` int(11) unsigned NOT NULL COMMENT 'Department ID',
  `category_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Category ID',
  `priority_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Priority ID',
  `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Product ID',
  `person_id` int(11) unsigned NOT NULL COMMENT 'The primary end-user this ticket belongs to',
  `agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The primary agent this ticket belongs to',
  `created_by` int(11) unsigned NOT NULL COMMENT 'The person or agent who created this ticket',
  `company_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The company this ticket is associated with',
  `status` enum('awaiting_agent','awaiting_user','pending','resolved','closed') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'awaiting_agent' COMMENT 'The status of the ticket',
  `created_at` int(11) unsigned NOT NULL COMMENT 'Timestamp when this ticket was created',
  `has_attachments` tinyint(1) NOT NULL DEFAULT '0',
  `has_flagged` tinyint(1) NOT NULL DEFAULT '0',
  `has_participants` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`agent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_search_data` (
  `field_id` int(11) unsigned NOT NULL,
  `ticket_id` int(11) unsigned NOT NULL,
  `value` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'An integer value. Eg. a timestamp',
  `input` text NOT NULL COMMENT 'A free string value',
  PRIMARY KEY (`field_id`,`ticket_id`),
  KEY `value` (`field_id`,`value`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_search_participants` (
  `ticket_id` int(11) unsigned NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`ticket_id`,`person_id`),
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tickets_search_subjects` (
  `ticket_id` int(11) unsigned NOT NULL,
  `subject` text NOT NULL,
  PRIMARY KEY (`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ticket_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `display_order` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ticket_departments` (
  `id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ticket_macros` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `labels` text NOT NULL COMMENT 'Space-separated list of labels',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `actions` mediumblob NOT NULL COMMENT 'Serialized PHP array of options',
  `is_global` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If not global, permissions come from perms table',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ticket_macros_perms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `macro_id` int(10) unsigned NOT NULL,
  `object_type` enum('department_id','usergroup_id','person_id') NOT NULL,
  `object_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `macro_id` (`macro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ticket_priorities` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `priority` smallint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The numeric priority, higher is more important',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `usergroups` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'A simple display name for the usergroup',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `usergroups_people` (
  `usergroup_id` int(11) unsigned NOT NULL,
  `person_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`usergroup_id`,`person_id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_masks` (
  `person_id` int(10) unsigned NOT NULL,
  `overrides` mediumblob NOT NULL COMMENT 'A serialized PHP array of permission overrides',
  PRIMARY KEY (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
