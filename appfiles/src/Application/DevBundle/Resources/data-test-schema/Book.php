<?php

$queries = array();

$queries[] = "CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `sizetype` enum('big','medium','small') NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `organization_id` (`organization_id`),
  KEY `user_id` (`user_id`),
  KEY `owner_id` (`owner_id`),
  KEY `sizetype` (`sizetype`),
  KEY `created_at` (`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queries[] = "CREATE TABLE `book_info` (
  `book_id` int(11) NOT NULL,
  `info_id` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `value_input` text NOT NULL,
  PRIMARY KEY (`book_id`,`info_id`),
  KEY `value` (`info_id`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queries[] = "CREATE TABLE `book_subscriptions` (
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queries[] = "CREATE TABLE `book_titles` (
  `book_id` int(11) NOT NULL,
  `title` char(200) NOT NULL,
  PRIMARY KEY (`book_id`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queries[] = "CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queries[] = "CREATE TABLE `user_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
