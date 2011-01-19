<?php

$queries = array();

$queries[] = "CREATE TABLE `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) DEFAULT NULL,
  `primary_email_id` int(11) DEFAULT NULL,
  `is_user` tinyint(1) NOT NULL,
  `is_tech` tinyint(1) NOT NULL,
  `full_name` longtext,
  `informal_name` longtext,
  `nick_name` longtext,
  `secret_string` varchar(40) NOT NULL,
  `timezome` varchar(50) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `salt` varchar(40) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `primary_email_id` (`primary_email_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";


$queries[] = "CREATE TABLE `person2company` (
  `person_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`person_id`,`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";


$queries[] = "CREATE TABLE `person_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";


$queries[] = "CREATE TABLE `person_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `is_validated` tinyint(1) NOT NULL,
  `comment` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `validated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";


$queries[] = "CREATE TABLE `tickets_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `tech_id` int(11) DEFAULT NULL,
  `language_id` int(11) NOT NULL,
  `subject` text NOT NULL,
  `status` enum('open','closed','hidden') NOT NULL DEFAULT 'open',
  `sub_status` enum('awaiting_tech','pending','spam') DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `opened_at` datetime NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  `pending_at` datetime DEFAULT NULL,
  `awaiting_tech_at` datetime DEFAULT NULL,
  `field_4` int(11) DEFAULT NULL,
  `field_5` int(11) DEFAULT NULL,
  `field_6` text,
  `field_7` text,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`),
  KEY `department_id` (`department_id`),
  KEY `category_id` (`category_id`),
  KEY `tech_id` (`tech_id`),
  KEY `language_id` (`language_id`),
  KEY `status` (`status`),
  KEY `sub_status` (`sub_status`),
  KEY `company_id` (`company_id`),
  KEY `opened_at` (`opened_at`),
  KEY `closed_at` (`closed_at`),
  KEY `field_4` (`field_4`),
  KEY `field_5` (`field_5`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";


$queries[] = "CREATE TABLE `ticket_participants` (
  `ticket_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`ticket_id`,`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";


$queries[] = "CREATE TABLE `ticket_search_fieldassoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `value_int` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id_value` (`field_id`,`value_int`),
  KEY `field_id` (`field_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1";

