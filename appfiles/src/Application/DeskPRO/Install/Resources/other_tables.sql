##BEGIN:create_table.tickets_search_active##
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
) ENGINE=MEMORY;

##BEGIN:create_table.tickets_search_subject##
CREATE TABLE `tickets_search_subject` (
  `ticket_id` int(11) NOT NULL,
  `subject` varchar(1000) NOT NULL,
  PRIMARY KEY (`ticket_id`)
) ENGINE=MyISAM;

##BEGIN:create_table.tickets_search_message##
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
) ENGINE=MyISAM;

##BEGIN:create_table.tickets_search_message_active##
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
) ENGINE=MyISAM;