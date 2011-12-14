<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192); 

$tables = array();

$tables['admin_session'] = "
CREATE TABLE `admin_session` (
  `sessionid` varchar(32) NOT NULL default '',
  `adminid` int(10) unsigned NOT NULL default '0',
  `useragent` varchar(250) NOT NULL default '',
  `lastactivity` int(10) unsigned NOT NULL default '0',
  `path` varchar(250) NOT NULL default '',
  `host` varchar(20) NOT NULL default '',
  `firstactivity` int(10) NOT NULL default '0',
  `extra` mediumtext NOT NULL,
  PRIMARY KEY  (`sessionid`),
  KEY `techid` (`adminid`)
)   ENGINE=MyISAM 
";

$tables['ban_email'] = "
CREATE TABLE `ban_email` (
  `email` varchar(250) NOT NULL default '',
  `tech` int(10) NOT NULL default '0',
  PRIMARY KEY  (`email`)
)   ENGINE=MyISAM 
";

$tables['billing_credit_bundles'] = "
CREATE TABLE `billing_credit_bundles` (
  `id` int(11) NOT NULL auto_increment,
  `require_credits` decimal(12,2) NOT NULL default '10.00',
  `free_credits` decimal(12,2) NOT NULL default '2.00',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['billing_order'] = "
CREATE TABLE `billing_order` (
  `id` int(11) NOT NULL auto_increment,
  `ref` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) default NULL,
  `summary` varchar(255) NOT NULL default '',
  `amount` decimal(12,2) NOT NULL default '0.00',
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) default NULL,
  `cart` text NOT NULL,
  `status` varchar(255) NOT NULL default 'pending',
  `process_status` varchar(255) NOT NULL default 'none',
  `log` text NOT NULL,
  `notes` text NOT NULL,
  `is_hidden` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ref_unqidx_idx` (`ref`),
  KEY `user_id_idx` (`user_id`),
  KEY `ticket_id_idx` (`ticket_id`),
  KEY `created_at_idx` (`created_at`),
  KEY `finished_at_idx` (`finished_at`),
  KEY `status_idx` (`status`),
  KEY `is_hidden_idx` (`is_hidden`)
)   ENGINE=MyISAM 
";

$tables['billing_rules'] = "
CREATE TABLE `billing_rules` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default NULL,
  `run_order` int(11) NOT NULL default '0',
  `criteria` text NOT NULL,
  `cost_ticket` decimal(12,2) NOT NULL default '0.00',
  `cost_reply` decimal(12,2) NOT NULL default '0.00',
  `cost_time` decimal(12,2) NOT NULL default '0.00',
  `is_set_amount` tinyint(1) NOT NULL default '0',
  `is_last` tinyint(1) NOT NULL default '1',
  `cancel_ids` text NOT NULL,
  `admin_comment` text NOT NULL,
  `user_comment` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent_id_idx` (`parent_id`)
)   ENGINE=MyISAM 
";

$tables['billing_transaction'] = "
CREATE TABLE `billing_transaction` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `billing_order_id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL default '',
  `payment_gateway_sysname` varchar(255) NOT NULL default '',
  `status` varchar(255) NOT NULL default 'pending',
  `gateway_status` varchar(255) NOT NULL default '',
  `amount` decimal(12,2) NOT NULL default '0.00',
  `currency` varchar(255) NOT NULL default 'USD',
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) default NULL,
  `details` text NOT NULL,
  `request` text NOT NULL,
  `response` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id_idx` (`user_id`),
  KEY `created_at_idx` (`created_at`),
  KEY `finished_at_idx` (`finished_at`),
  KEY `status_idx` (`status`),
  KEY `billing_order_id_idx` (`billing_order_id`)
)   ENGINE=MyISAM 
";

$tables['blob_merge'] = "
CREATE TABLE `blob_merge` (
  `blobid` int(10) NOT NULL,
  `tablename` varchar(250) NOT NULL,
  `tableid` int(10) NOT NULL,
  KEY `blobid` (`blobid`),
  KEY `tableid` (`tableid`,`tablename`)
)   ENGINE=MyISAM 
";

$tables['blob_parts'] = "
CREATE TABLE `blob_parts` (
  `blobid` int(10) NOT NULL default '0',
  `blobdata` longblob NOT NULL,
  `displayorder` int(10) NOT NULL default '0',
  KEY `blobid` (`blobid`)
)   ENGINE=MyISAM 
";

$tables['blobs'] = "
CREATE TABLE `blobs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `thumbnail` mediumblob NOT NULL,
  `filepath` text,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['calendar_def'] = "
CREATE TABLE `calendar_def` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) default NULL,
  `display_name` mediumtext NOT NULL,
  `description` mediumtext,
  `formtype` varchar(250) NOT NULL default 'input',
  `default_value` varchar(250) default NULL,
  `parsed_default_value` varchar(250) default NULL,
  `data` mediumtext NOT NULL,
  `maxoptions` smallint(4) NOT NULL default '0',
  `minoptions` smallint(4) NOT NULL default '0',
  `maxlength` smallint(6) NOT NULL default '0',
  `minlength` smallint(6) NOT NULL default '0',
  `regex` varchar(250) default NULL,
  `error_message` varchar(250) default NULL,
  `required` int(1) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `multiselect` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['calendar_task'] = "
CREATE TABLE `calendar_task` (
  `id` int(10) NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `startstamp` int(10) NOT NULL default '0',
  `endstamp` int(10) NOT NULL default '0',
  `description` mediumtext NOT NULL,
  `techmaker` int(10) NOT NULL default '0',
  `multistaff` int(1) NOT NULL default '0',
  `globalcomplete` int(1) NOT NULL default '0',
  `notifycompletion` int(1) NOT NULL default '0',
  `repeattype` int(1) NOT NULL default '0',
  `value1` int(10) NOT NULL default '0',
  `value2` varchar(250) NOT NULL default '0',
  `weekstart` int(10) NOT NULL default '0',
  `allday` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `repeattype` (`repeattype`)
)   ENGINE=MyISAM 
";

$tables['calendar_task_iteration'] = "
CREATE TABLE `calendar_task_iteration` (
  `task_techid` int(11) NOT NULL default '0',
  `taskid` int(11) NOT NULL default '0',
  `completed` int(11) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`taskid`,`task_techid`,`timestamp`)
)   ENGINE=MyISAM 
";

$tables['calendar_task_tech'] = "
CREATE TABLE `calendar_task_tech` (
  `id` int(10) NOT NULL auto_increment,
  `taskid` int(10) NOT NULL default '0',
  `email_due` int(1) NOT NULL default '0',
  `email_before1` int(3) NOT NULL default '0',
  `email_before2` int(3) NOT NULL default '0',
  `techid` int(1) NOT NULL default '0',
  `completed` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `taskid` (`taskid`,`techid`)
)   ENGINE=MyISAM 
";

$tables['chat_attachment'] = "
CREATE TABLE `chat_attachment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `blobid` int(10) unsigned NOT NULL,
  `techid` int(10) unsigned NOT NULL,
  `chatid` int(10) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filesize` varchar(255) NOT NULL,
  `extension` varchar(50) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['chat_canned'] = "
CREATE TABLE `chat_canned` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `techid` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['chat_chat'] = "
CREATE TABLE `chat_chat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ref` varchar(255) NOT NULL,
  `depid` int(10) unsigned NOT NULL,
  `authcode` varchar(32) NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `userdisplayname` varchar(255) NOT NULL,
  `useremail` varchar(255) NOT NULL,
  `sessionid` varchar(32) NOT NULL,
  `techid` int(10) unsigned NOT NULL,
  `subject` text NOT NULL,
  `rating` tinyint(100) NOT NULL default '-1',
  `feedback` text NOT NULL,
  `is_proactive_chat` tinyint(1) NOT NULL,
  `techid_proactive_chat` int(10) unsigned NOT NULL,
  `has_started` tinyint(1) NOT NULL default '1',
  `tech_typing` tinyint(1) NOT NULL default '0',
  `user_typing` varchar(255) NOT NULL default '0',
  `timestamp_ping` int(10) NOT NULL,
  `timestamp_timeout` int(11) NOT NULL,
  `timestamp_start` decimal(12,2) NOT NULL,
  `timestamp_end` decimal(12,2) NOT NULL,
  `timestamp_assigned` decimal(12,2) NOT NULL,
  `did_send_transcript` tinyint(1) NOT NULL default '0',
  `did_create_user` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`),
  KEY `timestamp_start` (`timestamp_start`),
  KEY `timestamp_end` (`timestamp_end`),
  KEY `timestamp_assigned` (`timestamp_assigned`),
  KEY `timestamp_ping` (`timestamp_ping`),
  FULLTEXT KEY `subject` (`subject`)
)   ENGINE=MyISAM 
";

$tables['chat_dep'] = "
CREATE TABLE `chat_dep` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `techs` text NOT NULL,
  `displayorder` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['chat_dep_perms'] = "
CREATE TABLE `chat_dep_perms` (
  `depid` int(10) unsigned NOT NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`depid`,`groupid`)
)   ENGINE=MyISAM 
";

$tables['chat_message'] = "
CREATE TABLE `chat_message` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `chatid` int(10) unsigned NOT NULL,
  `authorid` varchar(32) NOT NULL,
  `authortype` enum('user','tech','system') NOT NULL,
  `sessionid` varchar(32) NOT NULL,
  `authorname` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `formatting` enum('none','html') NOT NULL default 'none',
  `visibility` enum('all','tech','user') NOT NULL,
  `timestamp_sent` decimal(12,2) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `chatid` (`chatid`),
  KEY `timestamp_sent` (`timestamp_sent`),
  KEY `author` (`authortype`,`authorid`),
  FULLTEXT KEY `message` (`message`)
)   ENGINE=MyISAM 
";

$tables['cron_log'] = "
CREATE TABLE `cron_log` (
  `id` int(10) NOT NULL auto_increment,
  `scriptid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `overdue` int(10) NOT NULL default '0',
  `logtext` mediumtext NOT NULL,
  `logdetail` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['cron_options'] = "
CREATE TABLE `cron_options` (
  `id` int(10) NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `scriptname` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `nextrun` int(10) NOT NULL default '0',
  `frequency` varchar(250) NOT NULL default '',
  `custom` int(1) NOT NULL default '0',
  `lastrun` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `scriptname` (`scriptname`)
)   ENGINE=MyISAM 
";

$tables['data'] = "
CREATE TABLE `data` (
  `name` varchar(250) NOT NULL default '',
  `data` longtext NOT NULL,
  `isdefault` int(1) NOT NULL default '0',
  PRIMARY KEY  (`name`),
  KEY `isdefault` (`isdefault`)
)   ENGINE=MyISAM 
";

$tables['def_permissions'] = "
CREATE TABLE `def_permissions` (
  `permid` int(10) unsigned NOT NULL auto_increment,
  `id` int(10) unsigned NOT NULL,
  `tablename` varchar(25) NOT NULL,
  `usergroup` int(10) unsigned NOT NULL,
  `perm_type` varchar(255) NOT NULL,
  PRIMARY KEY  (`permid`),
  KEY `lookup` (`id`,`tablename`)
)   ENGINE=MyISAM 
";

$tables['deskpro_help_glossary'] = "
CREATE TABLE `deskpro_help_glossary` (
  `id` int(10) NOT NULL auto_increment,
  `word` varchar(250) NOT NULL default '',
  `content` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `word` (`word`)
)   ENGINE=MyISAM 
";

$tables['deskpro_help_tech_articles'] = "
CREATE TABLE `deskpro_help_tech_articles` (
  `id` int(10) NOT NULL auto_increment,
  `intname` varchar(250) NOT NULL default '',
  `title` varchar(250) NOT NULL default '',
  `content` mediumtext NOT NULL,
  `category` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `intname` (`intname`)
)   ENGINE=MyISAM 
";

$tables['deskpro_help_tech_cats'] = "
CREATE TABLE `deskpro_help_tech_cats` (
  `id` int(10) NOT NULL auto_increment,
  `intname` varchar(250) NOT NULL default '',
  `displayorder` int(10) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['deskpro_help_tooltip'] = "
CREATE TABLE `deskpro_help_tooltip` (
  `id` int(10) NOT NULL auto_increment,
  `section` varchar(250) NOT NULL default '',
  `tips` mediumtext NOT NULL,
  `mainhelp` mediumtext NOT NULL,
  `maintitle` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `section` (`section`)
)   ENGINE=MyISAM 
";

$tables['email_send'] = "
CREATE TABLE `email_send` (
  `id` int(10) NOT NULL auto_increment,
  `from_email` varchar(250) NOT NULL default '',
  `userid` int(10) NOT NULL default '0',
  `subject` mediumtext NOT NULL,
  `message` mediumtext NOT NULL,
  `timestamp_startsend` int(10) NOT NULL default '0',
  `timestamp_activity` int(10) NOT NULL default '0',
  `timestamp_completed` int(10) NOT NULL default '0',
  `total` int(10) NOT NULL default '0',
  `timestamp_created` int(10) NOT NULL default '0',
  `from_name` varchar(250) NOT NULL default '',
  `ref_name` varchar(250) NOT NULL default '',
  `ref_description` mediumtext NOT NULL,
  `log_message` int(1) NOT NULL default '0',
  `total_sent` int(10) NOT NULL default '0',
  `tech_aborted` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['email_send_log'] = "
CREATE TABLE `email_send_log` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `emailid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `emailid` (`emailid`)
)   ENGINE=MyISAM 
";

$tables['email_send_pending'] = "
CREATE TABLE `email_send_pending` (
  `id` int(10) NOT NULL auto_increment,
  `emailid` int(10) NOT NULL default '0',
  `userids` mediumtext NOT NULL,
  `number` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['error_log'] = "
CREATE TABLE `error_log` (
  `id` int(10) NOT NULL auto_increment,
  `timestamp` int(10) NOT NULL default '0',
  `type` varchar(250) NOT NULL default '',
  `details` mediumtext NOT NULL,
  `backtrace` text NOT NULL,
  `summary` text NOT NULL,
  `gateway_error` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`)
)   ENGINE=MyISAM 
";

$tables['escalate'] = "
CREATE TABLE `escalate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time_type` varchar(250) NOT NULL default '0',
  `time_amount` int(10) NOT NULL default '0',
  `criteria_category` int(10) NOT NULL default '0',
  `criteria_priority` int(10) NOT NULL default '0',
  `criteria_tech` int(10) NOT NULL default '0',
  `criteria_usergroup` varchar(250) NOT NULL,
  `criteria_useremail` varchar(250) NOT NULL,
  `actions_email_owner` int(1) NOT NULL default '0',
  `actions_pm_owner` int(1) NOT NULL default '0',
  `actions_email_techs` mediumtext NOT NULL,
  `actions_pm_techs` mediumtext NOT NULL,
  `actions_category` int(1) NOT NULL default '0',
  `actions_priority` int(1) NOT NULL default '0',
  `actions_tech` int(1) NOT NULL default '0',
  `repeat_this` int(1) NOT NULL default '0',
  `repeat_other` int(1) NOT NULL default '0',
  `criteria_workflow` int(1) NOT NULL default '0',
  `actions_workflow` int(1) NOT NULL default '0',
  `criteria_company` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['escalate_log'] = "
CREATE TABLE `escalate_log` (
  `id` int(10) NOT NULL auto_increment,
  `timestamp` int(10) NOT NULL default '0',
  `ticketid` int(10) NOT NULL default '0',
  `escalateid` int(10) NOT NULL default '0',
  `timestamp_criteria` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `ticketid` (`ticketid`,`timestamp_criteria`)
)   ENGINE=MyISAM 
";

$tables['failed_email'] = "
CREATE TABLE `failed_email` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` int(10) NOT NULL,
  `failcount` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['failed_email_part'] = "
CREATE TABLE `failed_email_part` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `failed_email_id` int(10) unsigned NOT NULL,
  `data` longblob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `failed_email_id` (`failed_email_id`)
)   ENGINE=MyISAM 
";

$tables['faq_article_resolve'] = "
CREATE TABLE `faq_article_resolve` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `articleid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `question_subject` text NOT NULL,
  `question_message` text NOT NULL,
  `timestamp_submitted` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `search` (`question_subject`,`question_message`)
)   ENGINE=MyISAM 
";

$tables['faq_articles'] = "
CREATE TABLE `faq_articles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(250) NOT NULL,
  `answer` mediumtext NOT NULL,
  `techid_made` int(10) NOT NULL default '0',
  `timestamp_made` int(10) NOT NULL default '0',
  `techid_modified` int(10) NOT NULL default '0',
  `timestamp_modified` int(10) NOT NULL default '0',
  `allow_comments` tinyint(1) NOT NULL default '1',
  `question` mediumtext NOT NULL,
  `category` int(10) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `to_validate` int(1) NOT NULL default '0',
  `keywords` mediumtext NOT NULL,
  `userid` int(10) NOT NULL default '0',
  `rating` int(10) NOT NULL default '0',
  `votes` int(10) NOT NULL default '0',
  `ref` varchar(20) NOT NULL default '',
  `views` int(10) NOT NULL default '0',
  `featured` int(1) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ref` (`ref`),
  KEY `category` (`category`),
  KEY `timestamp_made` (`timestamp_made`),
  KEY `timestamp_modified` (`timestamp_modified`),
  KEY `featured` (`featured`),
  KEY `to_validate` (`to_validate`,`category`),
  FULLTEXT KEY `search` (`title`,`question`,`answer`)
)   ENGINE=MyISAM 
";

$tables['faq_articles_related'] = "
CREATE TABLE `faq_articles_related` (
  `show_article` int(10) NOT NULL default '0',
  `related_article` int(10) NOT NULL default '0',
  PRIMARY KEY  (`show_article`,`related_article`)
)   ENGINE=MyISAM 
";

$tables['faq_attachments'] = "
CREATE TABLE `faq_attachments` (
  `id` int(10) NOT NULL auto_increment,
  `blobid` int(10) NOT NULL default '0',
  `filename` varchar(250) NOT NULL default '0',
  `filesize` varchar(250) NOT NULL default '0',
  `extension` varchar(10) NOT NULL default '0',
  `articleid` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `downloads` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `articleid` (`articleid`)
)   ENGINE=MyISAM 
";

$tables['faq_cats'] = "
CREATE TABLE `faq_cats` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) NOT NULL,
  `articles` int(10) NOT NULL default '0',
  `parent` int(10) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `timestamp_created` int(10) NOT NULL default '0',
  `timestamp_article_activity` int(10) NOT NULL default '0',
  `extracontent` mediumtext NOT NULL,
  `perm_inherit` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`)
)   ENGINE=MyISAM 
";

$tables['faq_cats_related'] = "
CREATE TABLE `faq_cats_related` (
  `show_cat` int(10) NOT NULL default '0',
  `related_cat` int(10) NOT NULL default '0',
  KEY `show_cat` (`show_cat`)
)   ENGINE=MyISAM 
";

$tables['faq_comments'] = "
CREATE TABLE `faq_comments` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `useremail` varchar(250) NOT NULL default '',
  `comments` mediumtext NOT NULL,
  `articleid` int(10) NOT NULL default '0',
  `timestamp_created` int(10) NOT NULL default '0',
  `published` int(1) NOT NULL default '0',
  `logged` int(1) NOT NULL default '0',
  `tech_publisher` int(10) NOT NULL default '0',
  `timestamp_published` int(10) NOT NULL default '0',
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `articleid` (`articleid`),
  KEY `logged` (`logged`)
)   ENGINE=MyISAM 
";

$tables['faq_def'] = "
CREATE TABLE `faq_def` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) default NULL,
  `display_name` mediumtext NOT NULL,
  `description` mediumtext,
  `formtype` varchar(250) NOT NULL default 'input',
  `data` mediumtext NOT NULL,
  `maxoptions` smallint(4) NOT NULL default '0',
  `minoptions` smallint(4) NOT NULL default '0',
  `maxlength` smallint(6) NOT NULL default '0',
  `minlength` smallint(6) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `multiselect` int(1) NOT NULL default '0',
  `display_name_language` mediumtext NOT NULL,
  `description_language` mediumtext NOT NULL,
  `tech_start` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['faq_keywords_articles'] = "
CREATE TABLE `faq_keywords_articles` (
  `articleid` int(10) NOT NULL default '0',
  `wordid` int(10) NOT NULL default '0',
  UNIQUE KEY `wordid` (`wordid`,`articleid`),
  KEY `articleid` (`articleid`)
)   ENGINE=MyISAM 
";

$tables['faq_keywords_words'] = "
CREATE TABLE `faq_keywords_words` (
  `wordid` int(250) NOT NULL auto_increment,
  `word` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`wordid`),
  UNIQUE KEY `word` (`word`)
)   ENGINE=MyISAM 
";

$tables['faq_permissions'] = "
CREATE TABLE `faq_permissions` (
  `catid` int(10) NOT NULL default '0',
  `groupid` int(10) NOT NULL default '0',
  UNIQUE KEY `groupid` (`groupid`,`catid`)
)   ENGINE=MyISAM 
";

$tables['faq_rating'] = "
CREATE TABLE `faq_rating` (
  `ipaddress` varchar(20) NOT NULL default '',
  `faqid` int(10) NOT NULL default '0',
  `rating` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `sessionid` varchar(32) NOT NULL default '',
  KEY `faqid` (`faqid`)
)   ENGINE=MyISAM 
";

$tables['faq_searchlog'] = "
CREATE TABLE `faq_searchlog` (
  `id` int(10) NOT NULL auto_increment,
  `timestamp` int(10) NOT NULL default '0',
  `query` mediumtext NOT NULL,
  `results` mediumtext NOT NULL,
  `total` int(10) NOT NULL default '0',
  `searchwords` varchar(250) NOT NULL default '',
  `sessionid` varchar(32) NOT NULL default '0',
  `solved_refs` mediumtext NOT NULL,
  `userid` int(10) NOT NULL default '0',
  `category` mediumtext NOT NULL,
  `extra` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `searchwords` (`searchwords`)
)   ENGINE=MyISAM 
";

$tables['faq_searchlog_solved'] = "
CREATE TABLE `faq_searchlog_solved` (
  `id` int(10) NOT NULL auto_increment,
  `articleid` int(10) NOT NULL default '0',
  `searchid` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `sessionid` varchar(32) NOT NULL default '',
  `solved` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `articleid` (`articleid`,`searchid`)
)   ENGINE=MyISAM 
";

$tables['faq_subscriptions'] = "
CREATE TABLE `faq_subscriptions` (
  `catid` int(10) NOT NULL default '0',
  `articleid` int(10) NOT NULL default '0',
  `new` int(1) NOT NULL default '0',
  `edit` int(1) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  KEY `catid` (`catid`),
  KEY `articleid` (`articleid`),
  KEY `userid` (`userid`)
)   ENGINE=MyISAM 
";

$tables['files'] = "
CREATE TABLE `files` (
  `id` int(10) NOT NULL auto_increment,
  `blobid` int(10) NOT NULL default '0',
  `faq_attach_id` int(10) NOT NULL default '0',
  `faq_id` int(10) NOT NULL default '0',
  `filename` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `category` int(10) NOT NULL default '0',
  `downloads` int(10) NOT NULL default '0',
  `filesize` varchar(250) NOT NULL default '',
  `extension` varchar(250) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['files_cats'] = "
CREATE TABLE `files_cats` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `displayorder` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['files_permissions'] = "
CREATE TABLE `files_permissions` (
  `catid` int(10) NOT NULL default '0',
  `groupid` int(10) NOT NULL default '0',
  UNIQUE KEY `groupid` (`groupid`,`catid`)
)   ENGINE=MyISAM 
";

$tables['gateway_decoded_parts'] = "
CREATE TABLE `gateway_decoded_parts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sourceid` int(10) unsigned NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sourceid` (`sourceid`)
)   ENGINE=MyISAM 
";

$tables['gateway_email_uid'] = "
CREATE TABLE `gateway_email_uid` (
  `uid` varchar(255) NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`uid`,`account_id`)
)   ENGINE=MyISAM 
";

$tables['gateway_emails'] = "
CREATE TABLE `gateway_emails` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `email` varchar(250) NOT NULL default '',
  `is_default` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['gateway_error'] = "
CREATE TABLE `gateway_error` (
  `id` int(10) NOT NULL auto_increment,
  `error` varchar(250) NOT NULL default '',
  `timestamp` int(10) NOT NULL default '0',
  `sourceid` int(10) NOT NULL default '0',
  `subject` varchar(250) NOT NULL default '',
  `email` varchar(250) NOT NULL default '',
  `gateway` varchar(250) NOT NULL default '',
  `error_log` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `error` (`error`)
)   ENGINE=MyISAM 
";

$tables['gateway_pop_accounts'] = "
CREATE TABLE `gateway_pop_accounts` (
  `id` int(11) NOT NULL auto_increment,
  `server` varchar(64) default NULL,
  `username` varchar(64) default NULL,
  `password` varchar(64) default NULL,
  `target` varchar(64) NOT NULL default 'user',
  `port` int(10) NOT NULL default '0',
  `usessl` int(1) NOT NULL default '0',
  `active` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `server` (`server`,`username`)
)   ENGINE=MyISAM 
";

$tables['gateway_pop_failures'] = "
CREATE TABLE `gateway_pop_failures` (
  `id` int(11) NOT NULL auto_increment,
  `accountid` int(10) NOT NULL default '0',
  `timestamp` int(11) default NULL,
  `error_id` int(10) NOT NULL default '0',
  `error_int_id` int(10) NOT NULL default '0',
  `error_text` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['gateway_source'] = "
CREATE TABLE `gateway_source` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `size` int(10) unsigned NOT NULL,
  `gateway` varchar(255) NOT NULL,
  `accountid` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `is_inserted` tinyint(1) NOT NULL default '0',
  `is_toobig` tinyint(1) NOT NULL default '0',
  `is_decoded` tinyint(1) NOT NULL default '0',
  `is_complete` tinyint(1) NOT NULL default '0',
  `in_process` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `is_inserted` (`is_inserted`),
  KEY `is_decoded` (`is_decoded`),
  KEY `in_process` (`in_process`),
  KEY `is_complete` (`is_complete`)
)   ENGINE=MyISAM 
";

$tables['gateway_source_parts'] = "
CREATE TABLE `gateway_source_parts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sourceid` int(10) unsigned NOT NULL,
  `source` blob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sourceid` (`sourceid`)
)   ENGINE=MyISAM 
";

$tables['gateway_spam'] = "
CREATE TABLE `gateway_spam` (
  `id` int(10) NOT NULL auto_increment,
  `type` varchar(250) NOT NULL default '',
  `regex` int(1) NOT NULL default '0',
  `textmatch` mediumtext NOT NULL,
  `action` enum('delete','spam') NOT NULL default 'spam',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['image_verify'] = "
CREATE TABLE `image_verify` (
  `ref` varchar(50) NOT NULL,
  `newcount` tinyint(4) NOT NULL default '0',
  `code` varchar(255) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ref`)
)   ENGINE=MyISAM 
";

$tables['images'] = "
CREATE TABLE `images` (
  `id` int(10) NOT NULL auto_increment,
  `blobid` int(10) NOT NULL default '0',
  `filename` varchar(250) NOT NULL default '',
  `filesize` int(10) NOT NULL default '0',
  `extension` varchar(50) NOT NULL default '',
  `content_type` varchar(250) NOT NULL default '',
  `content_id` int(10) NOT NULL default '0',
  `tempkey` varchar(50) NOT NULL default '',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `tempkey` (`tempkey`)
)   ENGINE=MyISAM 
";

$tables['languages'] = "
CREATE TABLE `languages` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `is_selectable` int(1) NOT NULL default '0',
  `isocode` varchar(250) NOT NULL default '',
  `contenttype` varchar(250) NOT NULL default '',
  `direction` enum('ltr','rtl') NOT NULL default 'ltr',
  `deskproid` varchar(20) NOT NULL default '',
  `linked_deskproid` varchar(20) NOT NULL,
  `has_submitted` tinyint(1) NOT NULL default '0',
  `version` varchar(20) NOT NULL,
  `base` int(1) NOT NULL default '0',
  `credits` mediumtext NOT NULL,
  `flag` varchar(50) NOT NULL,
  `master` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['manual_comments'] = "
CREATE TABLE `manual_comments` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `email` varchar(250) NOT NULL default '',
  `pageid` int(10) NOT NULL default '0',
  `timestamp_created` int(10) NOT NULL default '0',
  `timestamp_validated` int(10) NOT NULL default '0',
  `is_validated` int(1) NOT NULL default '0',
  `comments` mediumtext NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['manual_manual_styles'] = "
CREATE TABLE `manual_manual_styles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `manualid` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `element` varchar(250) NOT NULL,
  `attributes` text NOT NULL,
  `css` text NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['manual_manuals'] = "
CREATE TABLE `manual_manuals` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `published` int(1) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `time_gen_single` int(10) unsigned NOT NULL,
  `time_gen_print` int(10) unsigned NOT NULL,
  `time_gen_zip` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['manual_manuals_perms'] = "
CREATE TABLE `manual_manuals_perms` (
  `manualid` int(10) unsigned NOT NULL,
  `usergroup` int(10) unsigned NOT NULL,
  `perm_type` varchar(60) NOT NULL,
  PRIMARY KEY  (`manualid`,`usergroup`,`perm_type`),
  KEY `manualid` (`manualid`)
)   ENGINE=MyISAM 
";

$tables['manual_pages'] = "
CREATE TABLE `manual_pages` (
  `id` int(10) NOT NULL auto_increment,
  `parent` int(10) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `content` mediumtext NOT NULL,
  `revisionid` int(10) NOT NULL default '0',
  `timestamp_revision` int(10) NOT NULL default '0',
  `timestamp_creation` int(10) NOT NULL default '0',
  `manualid` int(1) NOT NULL default '0',
  `published` int(1) NOT NULL,
  `allow_comments` int(1) NOT NULL,
  `old_parent` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `manualid` (`manualid`),
  FULLTEXT KEY `content` (`content`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `allcontent` (`title`,`content`)
)   ENGINE=MyISAM 
";

$tables['manual_revisions'] = "
CREATE TABLE `manual_revisions` (
  `id` int(10) NOT NULL auto_increment,
  `pageid` int(10) NOT NULL default '0',
  `revisionid` int(10) NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['manual_searchlog'] = "
CREATE TABLE `manual_searchlog` (
  `id` int(10) NOT NULL auto_increment,
  `searchwords` varchar(250) NOT NULL default '',
  `matches` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['news'] = "
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(250) NOT NULL,
  `details` mediumtext NOT NULL,
  `techid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `logged_in` int(1) NOT NULL default '0',
  `logged_out` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['notebook_page'] = "
CREATE TABLE `notebook_page` (
  `id` int(11) NOT NULL auto_increment,
  `ref` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` int(11) NOT NULL,
  `expire_at` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ref` (`ref`),
  KEY `user_id` (`user_id`),
  KEY `expire_at` (`expire_at`)
)   ENGINE=MyISAM 
";

$tables['notebook_page2ticket'] = "
CREATE TABLE `notebook_page2ticket` (
  `ticket_id` int(11) NOT NULL,
  `notebook_page_id` int(11) NOT NULL,
  PRIMARY KEY  (`ticket_id`,`notebook_page_id`)
)   ENGINE=MyISAM 
";

$tables['notebook_page_attach'] = "
CREATE TABLE `notebook_page_attach` (
  `id` int(10) NOT NULL auto_increment,
  `filename` varchar(250) NOT NULL default '0',
  `filesize` varchar(250) NOT NULL default '0',
  `notebook_page_id` int(10) NOT NULL default '0',
  `blobid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `extension` varchar(5) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `ticketid` (`notebook_page_id`),
  KEY `extension` (`extension`)
)   ENGINE=MyISAM 
";

$tables['payment_gateway_logs'] = "
CREATE TABLE `payment_gateway_logs` (
  `id` int(11) NOT NULL auto_increment,
  `sysname` varchar(50) NOT NULL,
  `summary` varchar(255) NOT NULL default '',
  `details` text NOT NULL,
  `created_at` int(11) NOT NULL,
  `is_error_type` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `sysname_idx` (`sysname`),
  KEY `is_error_type_idx` (`is_error_type`)
)   ENGINE=MyISAM 
";

$tables['payment_gateways'] = "
CREATE TABLE `payment_gateways` (
  `sysname` varchar(50) NOT NULL,
  `classname` varchar(50) NOT NULL,
  `settings` text NOT NULL,
  `is_active` tinyint(1) NOT NULL default '0',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`sysname`)
)   ENGINE=MyISAM 
";

$tables['pipe_email_lookup'] = "
CREATE TABLE `pipe_email_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(255) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `email` (`email`)
)   ENGINE=MyISAM 
";

$tables['plugins'] = "
CREATE TABLE `plugins` (
  `id` int(10) NOT NULL auto_increment,
  `intname` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `installed` int(1) NOT NULL,
  `version` varchar(250) NOT NULL,
  `admin_url` varchar(255) NOT NULL,
  `info_url` varchar(255) NOT NULL,
  `plugin_dir` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `intname` (`intname`)
)   ENGINE=MyISAM 
";

$tables['query_log'] = "
CREATE TABLE `query_log` (
  `id` int(11) NOT NULL auto_increment,
  `query` mediumtext,
  `explain_log` mediumtext,
  `duration` decimal(15,10) default NULL,
  `stamp` int(11) default NULL,
  `filename` varchar(250) NOT NULL default '',
  `keytype` varchar(250) NOT NULL default '',
  `matches` int(10) NOT NULL default '0',
  `slow1` int(1) NOT NULL default '0',
  `slow2` int(1) NOT NULL default '0',
  `slow3` int(1) NOT NULL default '0',
  `slowmatches` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `duration` (`duration`),
  KEY `stamp` (`stamp`),
  KEY `matches` (`matches`)
)   ENGINE=MyISAM 
";

$tables['quickreply'] = "
CREATE TABLE `quickreply` (
  `id` int(10) NOT NULL auto_increment,
  `category` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `response` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['quickreply_cat'] = "
CREATE TABLE `quickreply_cat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `techid` int(10) NOT NULL default '0',
  `global` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['report'] = "
CREATE TABLE `report` (
  `id` int(10) NOT NULL auto_increment,
  `ref` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `lastrun` int(10) NOT NULL default '0',
  `format` varchar(250) NOT NULL default '',
  `email` varchar(250) NOT NULL default '',
  `repeattype` varchar(250) NOT NULL default '',
  `value1` varchar(250) NOT NULL default '',
  `value2` varchar(250) NOT NULL default '',
  `path` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['report_relations'] = "
CREATE TABLE `report_relations` (
  `reportid` int(10) NOT NULL default '0',
  `statid` int(10) NOT NULL default '0',
  `displayorder` int(11) NOT NULL
)   ENGINE=MyISAM 
";

$tables['report_stat'] = "
CREATE TABLE `report_stat` (
  `id` int(10) NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `variable1` varchar(250) NOT NULL default '',
  `variable2` varchar(250) NOT NULL default '',
  `datefield` varchar(250) NOT NULL default '',
  `generate_frequency` int(1) NOT NULL default '0',
  `generate_pie` int(1) NOT NULL default '0',
  `generate_bar` int(1) NOT NULL default '0',
  `generate_combined_frequency` int(1) NOT NULL default '0',
  `generate_combined_bar` int(1) NOT NULL default '0',
  `generate_split_frequency` int(1) NOT NULL default '0',
  `generate_split_pie` int(1) NOT NULL default '0',
  `generate_split_bar` int(1) NOT NULL default '0',
  `generate_ticketlist` int(1) NOT NULL default '0',
  `ticketlist_fields` mediumtext NOT NULL,
  `ref` varchar(250) NOT NULL default '',
  `displayorder` int(10) NOT NULL,
  `variable1_time` varchar(250) NOT NULL,
  `variable2_time` varchar(250) NOT NULL,
  `ticket_restrictions` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['search'] = "
CREATE TABLE `search` (
  `id` int(10) NOT NULL auto_increment,
  `results` mediumtext NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `total` int(10) NOT NULL default '0',
  `searchvalues` mediumtext NOT NULL,
  `total_page` int(10) NOT NULL default '0',
  `extra` text,
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['service_appkey'] = "
CREATE TABLE `service_appkey` (
  `appkey` varchar(50) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `access` text NOT NULL,
  PRIMARY KEY  (`appkey`)
)   ENGINE=MyISAM 
";

$tables['settings'] = "
CREATE TABLE `settings` (
  `display_name` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `value` mediumtext NOT NULL,
  `description` mediumtext,
  `field_type` varchar(250) NOT NULL,
  `displayorder` int(10) NOT NULL default '0',
  `options` mediumtext NOT NULL,
  `custom` int(1) NOT NULL default '0',
  `default_value` mediumtext NOT NULL,
  `category` varchar(250) NOT NULL default '',
  `php_verify` text NOT NULL,
  `php_generate` text NOT NULL,
  PRIMARY KEY  (`name`)
)   ENGINE=MyISAM 
";

$tables['settings_cat'] = "
CREATE TABLE `settings_cat` (
  `name` varchar(250) NOT NULL default '',
  `display_name` varchar(250) NOT NULL,
  `description` mediumtext NOT NULL,
  `displayorder` int(10) default '0',
  `parent` varchar(250) NOT NULL default '',
  `custom` int(1) NOT NULL default '0',
  PRIMARY KEY  (`name`)
)   ENGINE=MyISAM 
";

$tables['spam_filter_cat'] = "
CREATE TABLE `spam_filter_cat` (
  `id` int(10) unsigned NOT NULL,
  `word_count` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['spam_filter_doc'] = "
CREATE TABLE `spam_filter_doc` (
  `id` varchar(250) NOT NULL default '',
  `catid` int(10) unsigned NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`)
)   ENGINE=MyISAM 
";

$tables['spam_filter_word'] = "
CREATE TABLE `spam_filter_word` (
  `word` varchar(250) NOT NULL default '',
  `catid` int(10) unsigned NOT NULL,
  `count` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`word`,`catid`)
)   ENGINE=MyISAM 
";

$tables['style'] = "
CREATE TABLE `style` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `images` varchar(250) NOT NULL default '',
  `templateset` int(10) NOT NULL default '0',
  `header` mediumtext NOT NULL,
  `header_include` mediumtext NOT NULL,
  `footer` mediumtext NOT NULL,
  `header_unparsed` mediumtext NOT NULL,
  `header_include_unparsed` mediumtext NOT NULL,
  `footer_unparsed` mediumtext NOT NULL,
  `css` mediumtext NOT NULL,
  `extracss` mediumtext NOT NULL,
  `extracss_unparsed` mediumtext NOT NULL,
  `cssstyle` int(10) NOT NULL default '0',
  `active` int(1) NOT NULL default '0',
  `is_default` int(1) NOT NULL default '0',
  `elements` mediumtext NOT NULL,
  `ref` varchar(250) NOT NULL default '',
  `css_rtl` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['tech'] = "
CREATE TABLE `tech` (
  `id` int(10) NOT NULL auto_increment,
  `username` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `salt` varchar(15) NOT NULL,
  `email` varchar(250) NOT NULL,
  `sms` varchar(250) NOT NULL default '',
  `is_admin` int(1) NOT NULL default '0',
  `deny_normal_access` tinyint(1) NOT NULL default '0',
  `name` varchar(250) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `signature` mediumtext NOT NULL,
  `active` int(1) NOT NULL default '0',
  `disabled_reason` varchar(250) NOT NULL default '',
  `password_cookie` varchar(8) NOT NULL default '',
  `rsspassword` varchar(20) NOT NULL default '',
  `timezone` varchar(4) NOT NULL default '',
  `timezone_dst` int(1) NOT NULL default '0',
  `dst_auto_adjust` tinyint(1) NOT NULL default '1',
  `cats_admin` mediumtext NOT NULL,
  `cats_user` mediumtext NOT NULL,
  `fields_show_search` text NOT NULL,
  `info_dismiss` text NOT NULL,
  `weekstart` int(1) NOT NULL default '7',
  `footer` enum('ticket_search','ticket_select','saved_searches','users','links','ticket_control') NOT NULL default 'ticket_search',
  `forum_expire` int(10) NOT NULL default '0',
  `last_activity` int(10) unsigned NOT NULL,
  `email_tpl` varchar(50) NOT NULL,
  `email2_tpl` varchar(50) NOT NULL,
  `searchresults` int(10) NOT NULL default '20',
  `read_walkthrough` int(1) NOT NULL default '0',
  `frames` int(1) NOT NULL default '0',
  `defaultview` int(10) NOT NULL default '0',
  `userfield_selection` mediumtext NOT NULL,
  `email_on_login` int(1) NOT NULL default '0',
  `email_on_failed_login` tinyint(1) NOT NULL default '0',
  `ticketview_faq` int(1) NOT NULL default '0',
  `ticketview_messages` int(10) NOT NULL default '0',
  `front_select` int(1) NOT NULL default '0',
  `front_count` int(10) NOT NULL default '0',
  `front_own_1` varchar(250) NOT NULL default '',
  `front_own_2` varchar(250) NOT NULL default '',
  `front_own_3` varchar(250) NOT NULL default '',
  `front_new_1` varchar(250) NOT NULL default '',
  `front_new_2` varchar(250) NOT NULL default '',
  `front_new_3` varchar(250) NOT NULL default '',
  `front_other_1` varchar(250) NOT NULL default '',
  `front_other_2` varchar(250) NOT NULL default '',
  `front_other_3` varchar(250) NOT NULL default '',
  `front_part_1` varchar(255) NOT NULL,
  `front_part_2` varchar(255) NOT NULL,
  `front_part_3` varchar(255) NOT NULL,
  `alert_pm` int(1) NOT NULL default '0',
  `alert_reply_your` int(1) NOT NULL default '0',
  `alert_reply_cat` int(1) NOT NULL default '0',
  `alert_reply_all` int(1) NOT NULL default '0',
  `alert_new_cat` int(1) NOT NULL default '0',
  `alert_new_all` int(1) NOT NULL default '0',
  `alert_sound` varchar(250) NOT NULL default '',
  `alert_popup` int(1) NOT NULL default '0',
  `alert_time` int(10) NOT NULL default '0',
  `alert_frequency` int(2) NOT NULL default '0',
  `forum_email_newtopics` int(1) NOT NULL default '0',
  `forum_email_newmessages` int(1) NOT NULL default '0',
  `forum_email_mynewmessages` int(1) NOT NULL default '0',
  `email_tech_reply` int(1) NOT NULL default '0',
  `email_user_registered` int(1) NOT NULL default '0',
  `email_user_registered_validation` int(1) NOT NULL default '0',
  `email_new_email` int(1) NOT NULL default '0',
  `email_new_sms` int(1) NOT NULL default '0',
  `email_reply_email` int(1) NOT NULL default '0',
  `email_reply_sms` int(1) NOT NULL default '0',
  `email_own_email` int(1) NOT NULL default '0',
  `email_own_sms` int(1) NOT NULL default '0',
  `email_assigned` int(1) NOT NULL default '0',
  `email_pm` int(1) NOT NULL default '0',
  `email_attachments` int(1) NOT NULL default '0',
  `email_own_attachments` int(1) NOT NULL default '0',
  `email_faq` int(1) NOT NULL default '0',
  `email_add_participant` tinyint(1) NOT NULL default '0',
  `email_reply_participant` tinyint(1) NOT NULL default '0',
  `p_approve_new_registrations` int(1) NOT NULL default '0',
  `p_close_ticket` int(1) NOT NULL default '0',
  `p_merge_ticket` int(1) NOT NULL default '0',
  `p_manage_participants` tinyint(1) NOT NULL default '0',
  `p_manage_participants_other` tinyint(1) NOT NULL default '0',
  `p_tech_view` int(1) NOT NULL default '0',
  `p_tech_edit` int(1) NOT NULL default '0',
  `p_add_k` int(1) NOT NULL default '0',
  `p_publish_k` int(1) NOT NULL default '0',
  `p_edit_k` int(1) NOT NULL default '0',
  `p_delete_k` int(1) NOT NULL default '0',
  `p_add_c_k` int(1) NOT NULL default '0',
  `p_delete_c_k` int(1) NOT NULL default '0',
  `p_edit_c_k` int(1) NOT NULL default '0',
  `p_create_users` int(1) NOT NULL default '0',
  `p_edit_users` int(1) NOT NULL default '0',
  `p_delete_users` int(1) NOT NULL default '0',
  `p_add_announcements` int(1) NOT NULL default '0',
  `p_delete_announcements` int(1) NOT NULL default '0',
  `p_edit_announcements` int(1) NOT NULL default '0',
  `p_delete_other` int(1) NOT NULL default '0',
  `p_delete_own` int(1) NOT NULL default '0',
  `p_user_expire` int(1) NOT NULL default '0',
  `p_quickedit_cats` int(1) NOT NULL default '0',
  `p_quickedit` int(1) NOT NULL default '0',
  `p_global_note` int(1) NOT NULL default '0',
  `p_forum_newforum` int(1) NOT NULL default '0',
  `p_forum_newtopic` int(1) NOT NULL default '0',
  `p_forum_replytopic` int(1) NOT NULL default '0',
  `p_unlock` int(1) NOT NULL default '0',
  `p_change_email` int(1) NOT NULL default '0',
  `p_change_signature` int(1) NOT NULL default '0',
  `p_change_password` int(1) NOT NULL default '0',
  `p_ticket_filters` int(1) NOT NULL default '0',
  `p_ticket_views` int(1) NOT NULL default '0',
  `p_add_t` int(1) NOT NULL default '0',
  `p_publish_t` int(1) NOT NULL default '0',
  `p_edit_t` int(1) NOT NULL default '0',
  `p_delete_t` int(1) NOT NULL default '0',
  `p_add_f` int(1) NOT NULL default '0',
  `p_add_c_f` int(1) NOT NULL default '0',
  `p_delete_f` int(1) NOT NULL default '0',
  `p_delete_c_f` int(1) NOT NULL default '0',
  `p_start_ticket` int(1) NOT NULL default '0',
  `p_add_technews` int(1) NOT NULL default '0',
  `p_edit_technews` int(1) NOT NULL default '0',
  `p_delete_technews` int(1) NOT NULL default '0',
  `p_ticketlog` int(1) NOT NULL default '0',
  `p_unassigned_view` int(1) NOT NULL default '0',
  `p_open_ticket` int(1) NOT NULL default '0',
  `p_tech_reply` int(1) NOT NULL,
  `p_man_create` int(1) NOT NULL default '0',
  `p_man_edit` int(1) NOT NULL default '0',
  `p_man_del` int(1) NOT NULL default '0',
  `p_manpage_create` int(1) NOT NULL default '0',
  `p_manpage_edit` int(1) NOT NULL default '0',
  `p_manpage_del` int(1) NOT NULL default '0',
  `p_mancomment_manage` int(1) NOT NULL default '0',
  `chat_timestamp_ping` int(10) NOT NULL,
  `chat_away` tinyint(1) NOT NULL default '0',
  `chat_prefs` text NOT NULL,
  `chat_state` text NOT NULL,
  `chat_timestamp_lastauto` int(11) NOT NULL,
  `p_chat` int(1) NOT NULL default '0',
  `p_chat_global_canned` int(1) NOT NULL default '0',
  `p_chat_del_logs` tinyint(1) NOT NULL default '0',
  `email_secondary` varchar(250) NOT NULL,
  `user_search_settings` mediumtext NOT NULL,
  `email_note` int(1) NOT NULL,
  `p_reports` int(1) NOT NULL,
  `p_assign_task` int(1) NOT NULL,
  `p_forum_deleteforum` int(1) NOT NULL,
  `notifier_plugin` int(1) NOT NULL default '0',
  `notifier_1_level` varchar(60) NOT NULL default 'category',
  `notifier_2_level` varchar(60) NOT NULL default 'workflow',
  `p_use_js` int(1) NOT NULL default '0',
  `p_ideas_edit` tinyint(1) NOT NULL default '0',
  `p_ideas_delete` tinyint(1) NOT NULL default '0',
  `p_ideas_comment` tinyint(1) NOT NULL default '0',
  `p_ideas_comment_delete` tinyint(1) NOT NULL default '0',
  `pref_afterreply_redirect` varchar(20) NOT NULL default 'search',
  `admin_loginlockout_override` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `display_name` (`display_name`)
)   ENGINE=MyISAM 
";

$tables['tech_attachments'] = "
CREATE TABLE `tech_attachments` (
  `id` int(10) NOT NULL auto_increment,
  `blobid` int(10) NOT NULL default '0',
  `filename` varchar(250) NOT NULL default '',
  `filesize` varchar(50) NOT NULL default '',
  `techid` int(10) NOT NULL default '0',
  `category` int(10) NOT NULL default '0',
  `extension` varchar(10) NOT NULL default '',
  `timestamp` int(10) NOT NULL default '0',
  `comments` mediumtext NOT NULL,
  `isglobal` int(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`),
  KEY `category` (`category`)
)   ENGINE=MyISAM 
";

$tables['tech_bookmarks'] = "
CREATE TABLE `tech_bookmarks` (
  `id` int(10) NOT NULL auto_increment,
  `url` varchar(250) NOT NULL default '',
  `comments` mediumtext NOT NULL,
  `displayorder` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  `category` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`,`category`)
)   ENGINE=MyISAM 
";

$tables['tech_email'] = "
CREATE TABLE `tech_email` (
  `techid` int(10) NOT NULL default '0',
  `fieldname` varchar(250) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  `newreply` int(1) NOT NULL default '0',
  `newticket` int(1) NOT NULL default '0',
  `email` int(1) NOT NULL default '0',
  `sms` int(1) NOT NULL default '0',
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['tech_folders'] = "
CREATE TABLE `tech_folders` (
  `techid` int(10) NOT NULL default '0',
  `type` varchar(250) NOT NULL default '',
  `categories` mediumtext NOT NULL,
  UNIQUE KEY `techid` (`techid`,`type`)
)   ENGINE=MyISAM 
";

$tables['tech_forum_forum'] = "
CREATE TABLE `tech_forum_forum` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['tech_forum_message'] = "
CREATE TABLE `tech_forum_message` (
  `id` int(10) NOT NULL auto_increment,
  `techid` int(10) NOT NULL default '0',
  `topicid` int(10) NOT NULL default '0',
  `message` text NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `topicid` (`topicid`)
)   ENGINE=MyISAM 
";

$tables['tech_forum_topic'] = "
CREATE TABLE `tech_forum_topic` (
  `id` int(10) NOT NULL auto_increment,
  `techid` int(10) NOT NULL default '0',
  `techid_lastreply` int(10) NOT NULL default '0',
  `created_timestamp` int(10) NOT NULL default '0',
  `message_timestamp` varchar(10) NOT NULL default '',
  `title` varchar(250) NOT NULL default '',
  `forumid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `forumid` (`forumid`),
  KEY `message_timestamp` (`message_timestamp`),
  FULLTEXT KEY `title` (`title`)
)   ENGINE=MyISAM 
";

$tables['tech_forum_view'] = "
CREATE TABLE `tech_forum_view` (
  `id` int(10) NOT NULL auto_increment,
  `topicid` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `topicid` (`topicid`,`techid`)
)   ENGINE=MyISAM 
";

$tables['tech_help_cat'] = "
CREATE TABLE `tech_help_cat` (
  `id` int(10) NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `displayorder` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['tech_help_entry'] = "
CREATE TABLE `tech_help_entry` (
  `id` int(10) NOT NULL auto_increment,
  `category` int(10) NOT NULL default '0',
  `entry` mediumtext NOT NULL,
  `displayorder` int(10) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `category` (`category`)
)   ENGINE=MyISAM 
";

$tables['tech_login_log'] = "
CREATE TABLE `tech_login_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `techid` int(10) unsigned NOT NULL,
  `ipaddress` varchar(250) NOT NULL,
  `altipaddress` varchar(250) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `section` enum('tech','admin') NOT NULL,
  `is_failed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['tech_news'] = "
CREATE TABLE `tech_news` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(250) NOT NULL,
  `details` mediumtext NOT NULL,
  `techid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `frontpage` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['tech_news_read'] = "
CREATE TABLE `tech_news_read` (
  `techid` int(10) NOT NULL default '0',
  `newsid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`techid`,`newsid`)
)   ENGINE=MyISAM 
";

$tables['tech_notes'] = "
CREATE TABLE `tech_notes` (
  `id` int(10) NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `note` mediumtext NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `category` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`,`category`)
)   ENGINE=MyISAM 
";

$tables['tech_pms'] = "
CREATE TABLE `tech_pms` (
  `id` int(11) NOT NULL auto_increment,
  `fromid` int(10) NOT NULL default '0',
  `toid` int(10) NOT NULL default '0',
  `is_read` int(1) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `message` mediumtext NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `toid` (`toid`)
)   ENGINE=MyISAM 
";

$tables['tech_sendmail'] = "
CREATE TABLE `tech_sendmail` (
  `id` int(10) NOT NULL auto_increment,
  `parent` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `subject` varchar(250) NOT NULL,
  `message` mediumtext NOT NULL,
  `from_email` varchar(250) NOT NULL,
  `to_email` varchar(250) NOT NULL,
  `tracking` int(1) NOT NULL default '0',
  `awaiting_reply` int(1) NOT NULL default '0',
  `pass` varchar(7) NOT NULL default '',
  `date_sent` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['tech_session'] = "
CREATE TABLE `tech_session` (
  `sessionid` varchar(32) NOT NULL default '',
  `techid` int(10) unsigned NOT NULL default '0',
  `useragent` varchar(250) NOT NULL default '',
  `lastactivity` int(10) unsigned NOT NULL default '0',
  `techzone` int(1) default '0',
  `path` varchar(250) NOT NULL default '',
  `host` varchar(20) NOT NULL default '',
  `firstactivity` int(10) NOT NULL default '0',
  `extra` mediumtext NOT NULL,
  PRIMARY KEY  (`sessionid`),
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['tech_start_tickets'] = "
CREATE TABLE `tech_start_tickets` (
  `techid` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['tech_ticket_save'] = "
CREATE TABLE `tech_ticket_save` (
  `ticketid` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `message` mediumtext NOT NULL,
  `category` int(10) NOT NULL default '0',
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ticketid` (`ticketid`,`techid`),
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['tech_ticket_watch'] = "
CREATE TABLE `tech_ticket_watch` (
  `id` int(10) NOT NULL auto_increment,
  `ticketid` int(10) NOT NULL default '0',
  `timestamp_created` int(10) NOT NULL default '0',
  `timestamp_complete` int(10) default '0',
  `completed` int(1) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`,`timestamp_created`)
)   ENGINE=MyISAM 
";

$tables['tech_timelog'] = "
CREATE TABLE `tech_timelog` (
  `id` int(10) NOT NULL auto_increment,
  `techid` int(10) NOT NULL default '0',
  `startstamp` int(10) NOT NULL default '0',
  `endstamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['tech_token'] = "
CREATE TABLE `tech_token` (
  `id` int(10) NOT NULL auto_increment,
  `techid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `token_type` varchar(250) NOT NULL default '',
  `token_value` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `timestamp` (`timestamp`)
)   ENGINE=MyISAM 
";

$tables['template'] = "
CREATE TABLE `template` (
  `name` varchar(250) NOT NULL,
  `template` mediumtext NOT NULL,
  `templateset` int(1) NOT NULL default '0',
  `category` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `upgraded` int(1) NOT NULL default '0',
  `changed` int(1) NOT NULL default '0',
  `custom` int(1) NOT NULL default '0',
  `template_unparsed` mediumtext NOT NULL,
  `backup` mediumtext NOT NULL,
  UNIQUE KEY `name` (`name`,`templateset`)
)   ENGINE=MyISAM 
";

$tables['template_attachments'] = "
CREATE TABLE `template_attachments` (
  `id` int(11) NOT NULL auto_increment,
  `blobid` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `extension` varchar(50) NOT NULL,
  `filesize` int(11) NOT NULL default '0',
  `created_at` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['template_cat'] = "
CREATE TABLE `template_cat` (
  `intname` varchar(250) NOT NULL default '',
  `name` varchar(250) NOT NULL,
  `description` mediumtext NOT NULL,
  `displayorder` int(10) NOT NULL default '0',
  `custom` int(1) NOT NULL default '0',
  PRIMARY KEY  (`intname`)
)   ENGINE=MyISAM 
";

$tables['template_replace'] = "
CREATE TABLE `template_replace` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `match_string` varchar(75) NOT NULL default '',
  `templateset` int(1) NOT NULL default '0',
  `replace_string` mediumtext NOT NULL,
  `evaluate` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['template_set'] = "
CREATE TABLE `template_set` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `parent` int(10) NOT NULL default '0',
  `ref` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['template_stylesheets'] = "
CREATE TABLE `template_stylesheets` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `stylesheet` mediumtext NOT NULL,
  `editor` mediumtext NOT NULL,
  `ref` varchar(250) NOT NULL default '',
  `stylesheet_rtl` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['template_tech_email'] = "
CREATE TABLE `template_tech_email` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `template` mediumtext,
  `description` varchar(250) NOT NULL,
  `upgraded` int(1) NOT NULL default '0',
  `changed` int(1) NOT NULL default '0',
  `custom` int(1) NOT NULL default '0',
  `version_upgrade` int(1) NOT NULL default '0',
  `template_unparsed` mediumtext NOT NULL,
  `subject` varchar(250) NOT NULL default '',
  `backup_template` mediumtext NOT NULL,
  `backup_subject` varchar(250) NOT NULL default '',
  `subject_unparsed` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
)   ENGINE=MyISAM 
";

$tables['template_user_email'] = "
CREATE TABLE `template_user_email` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `template` mediumtext,
  `description` varchar(250) NOT NULL,
  `upgraded` int(1) NOT NULL default '0',
  `changed` int(1) NOT NULL default '0',
  `custom` int(1) NOT NULL default '0',
  `version_upgrade` int(1) NOT NULL default '0',
  `template_unparsed` mediumtext NOT NULL,
  `subject` varchar(250) NOT NULL default '',
  `backup_template` mediumtext NOT NULL,
  `backup_subject` varchar(250) NOT NULL default '',
  `emailtype` varchar(250) NOT NULL default '',
  `subject_unparsed` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
)   ENGINE=MyISAM 
";

$tables['template_words'] = "
CREATE TABLE `template_words` (
  `wordref` varchar(50) NOT NULL default '',
  `language` int(10) NOT NULL default '0',
  `text` mediumtext NOT NULL,
  `cust` int(1) NOT NULL default '0',
  `backuptext` mediumtext NOT NULL,
  `version` int(10) NOT NULL,
  PRIMARY KEY  (`language`,`wordref`)
)   ENGINE=MyISAM 
";

$tables['template_words_cat'] = "
CREATE TABLE `template_words_cat` (
  `intname` varchar(250) NOT NULL default '',
  `name` varchar(250) NOT NULL default '',
  `displayorder` int(10) NOT NULL default '0'
)   ENGINE=MyISAM 
";

$tables['template_words_cat_link'] = "
CREATE TABLE `template_words_cat_link` (
  `wordref` varchar(250) NOT NULL default '',
  `category` varchar(250) NOT NULL default '0',
  PRIMARY KEY  (`wordref`)
)   ENGINE=MyISAM 
";

$tables['ticket'] = "
CREATE TABLE `ticket` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `closed_techid` int(10) NOT NULL default '0',
  `closed_user` int(10) NOT NULL default '0',
  `subject` varchar(250) NOT NULL default '',
  `userid` int(10) NOT NULL default '0',
  `tech` int(10) NOT NULL default '0',
  `btech` int(10) unsigned NOT NULL,
  `btech_handle` tinyint(1) NOT NULL default '0',
  `tech_creator` int(10) unsigned NOT NULL default '0',
  `category` int(10) NOT NULL default '0',
  `priority` int(10) NOT NULL default '0',
  `language` int(10) NOT NULL default '0',
  `is_locked` int(1) NOT NULL default '0',
  `timestamp_opened` int(10) NOT NULL default '0',
  `timestamp_closed` int(10) NOT NULL default '0',
  `timestamp_lastreply_user` int(10) NOT NULL default '0',
  `timestamp_lastreply_tech` int(10) NOT NULL default '0',
  `timestamp_locked` int(10) NOT NULL default '0',
  `lock_techid` int(10) NOT NULL default '0',
  `ref` varchar(20) NOT NULL default '',
  `ticketemail` varchar(250) NOT NULL default '',
  `nodisplay` varchar(50) default NULL,
  `rating` int(1) NOT NULL default '0',
  `timestamp_rating` int(10) NOT NULL default '0',
  `workflow` int(10) NOT NULL default '0',
  `timestamp_user_waiting` int(10) NOT NULL default '0',
  `total_user_waiting` int(10) NOT NULL default '0',
  `timestamp_tech_waiting` int(10) NOT NULL default '0',
  `status` enum('awaiting_tech','awaiting_user','closed','nodisplay') NOT NULL default 'awaiting_tech',
  `timestamp_lastreply` int(10) NOT NULL default '0',
  `timestamp_first_tech_reply` int(10) NOT NULL default '0',
  `accountid` int(10) NOT NULL default '0',
  `auto_reply` int(1) NOT NULL default '0',
  `auto_new` int(1) NOT NULL default '0',
  `authcode` varchar(250) NOT NULL default '',
  `creation` enum('web','gateway','tech','split') NOT NULL default 'web',
  `pinned` int(1) NOT NULL,
  `next_reply_billed` tinyint(1) NOT NULL default '0',
  `company` int(10) NOT NULL,
  `rule_mail_id` int(10) NOT NULL,
  `kb_suggest` enum('no','sent','should_send') NOT NULL default 'no',
  `is_autospam` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ref` (`ref`),
  KEY `userid` (`userid`),
  KEY `category` (`category`),
  KEY `is_locked` (`is_locked`),
  KEY `tech` (`tech`,`status`),
  KEY `status` (`status`,`category`),
  KEY `timestamp_opened` (`timestamp_opened`),
  KEY `timestamp_closed` (`timestamp_closed`),
  KEY `priority` (`priority`,`status`),
  KEY `pinned` (`pinned`),
  KEY `company` (`company`),
  KEY `btech` (`btech`),
  KEY `btech_handle` (`btech_handle`),
  FULLTEXT KEY `subject` (`subject`)
)   ENGINE=MyISAM 
";

$tables['ticket_attachments'] = "
CREATE TABLE `ticket_attachments` (
  `id` int(10) NOT NULL auto_increment,
  `filename` varchar(250) NOT NULL default '0',
  `filesize` varchar(250) NOT NULL default '0',
  `ticketid` int(10) NOT NULL default '0',
  `blobid` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `temporaryid` varchar(20) NOT NULL default '',
  `temporarytype` varchar(25) NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  `extension` varchar(5) NOT NULL default '',
  `techtmp` int(1) NOT NULL default '0',
  `messageid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `ticketid` (`ticketid`),
  KEY `extension` (`extension`)
)   ENGINE=MyISAM 
";

$tables['ticket_cat'] = "
CREATE TABLE `ticket_cat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(10) unsigned NOT NULL,
  `name` varchar(250) default NULL,
  `displayorder` int(10) NOT NULL default '0',
  `name_language` mediumtext NOT NULL,
  `custom_inherit` int(1) NOT NULL,
  `custom_all` int(1) NOT NULL,
  `perm_inherit` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['ticket_cat_permissions'] = "
CREATE TABLE `ticket_cat_permissions` (
  `usergroup` int(10) unsigned NOT NULL,
  `category` int(11) NOT NULL,
  PRIMARY KEY  (`usergroup`,`category`)
)   ENGINE=MyISAM 
";

$tables['ticket_def'] = "
CREATE TABLE `ticket_def` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) default NULL,
  `display_name` mediumtext NOT NULL,
  `description` mediumtext,
  `formtype` varchar(250) NOT NULL default 'input',
  `default_value` varchar(250) default NULL,
  `parsed_default_value` varchar(250) default NULL,
  `data` mediumtext NOT NULL,
  `maxoptions` smallint(4) NOT NULL default '0',
  `minoptions` smallint(4) NOT NULL default '0',
  `maxlength` smallint(6) NOT NULL default '0',
  `minlength` smallint(6) NOT NULL default '0',
  `regex` varchar(250) default NULL,
  `error_message` mediumtext,
  `required` int(1) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `multiselect` int(1) NOT NULL default '0',
  `display_name_language` mediumtext NOT NULL,
  `description_language` mediumtext NOT NULL,
  `error_language` mediumtext NOT NULL,
  `php_default_value` mediumtext NOT NULL,
  `is_global` tinyint(1) NOT NULL default '0',
  `show_on_search` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['ticket_def_cat'] = "
CREATE TABLE `ticket_def_cat` (
  `fieldid` int(11) NOT NULL,
  `catid` int(11) NOT NULL,
  PRIMARY KEY  (`fieldid`,`catid`)
)   ENGINE=MyISAM 
";

$tables['ticket_delete_log'] = "
CREATE TABLE `ticket_delete_log` (
  `id` int(10) NOT NULL auto_increment,
  `techid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `reason` mediumtext NOT NULL,
  `ticketref` varchar(250) NOT NULL default '',
  `subject` varchar(250) NOT NULL default '',
  `ticketid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `ticketref` (`ticketref`),
  KEY `ticketid` (`ticketid`)
)   ENGINE=MyISAM 
";

$tables['ticket_fielddisplay'] = "
CREATE TABLE `ticket_fielddisplay` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `code` mediumtext NOT NULL,
  `code_type` varchar(3) NOT NULL default 'var',
  `name` varchar(250) NOT NULL default '',
  `example` varchar(250) NOT NULL default '',
  `description` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['ticket_filters'] = "
CREATE TABLE `ticket_filters` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `techid` int(10) NOT NULL default '0',
  `save_name` varchar(250) NOT NULL,
  `data` mediumtext,
  `isglobal` int(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['ticket_log'] = "
CREATE TABLE `ticket_log` (
  `id` int(10) NOT NULL auto_increment,
  `ticketid` int(10) NOT NULL default '0',
  `actionlog` varchar(250) NOT NULL default '',
  `techid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `id_before` int(10) NOT NULL default '0',
  `id_after` int(10) NOT NULL default '0',
  `detail_before` mediumtext NOT NULL,
  `detail_after` mediumtext NOT NULL,
  `extra` varchar(250) NOT NULL default '',
  `agent` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `ticketid` (`ticketid`),
  KEY `techid` (`techid`),
  KEY `timestamp` (`timestamp`)
)   ENGINE=MyISAM 
";

$tables['ticket_merge'] = "
CREATE TABLE `ticket_merge` (
  `old_id` int(10) NOT NULL default '0',
  `old_ref` varchar(20) NOT NULL default '',
  `old_authcode` varchar(250) NOT NULL,
  `new_id` int(10) NOT NULL default '0',
  `new_ref` varchar(20) NOT NULL default ''
)   ENGINE=MyISAM 
";

$tables['ticket_message'] = "
CREATE TABLE `ticket_message` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ticketid` int(10) NOT NULL default '0',
  `message` mediumtext,
  `timestamp` int(10) default '0',
  `techid` int(10) NOT NULL default '0',
  `ipaddress` varchar(16) NOT NULL default '',
  `sourceid` int(10) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `note` mediumtext NOT NULL,
  `charset` varchar(250) NOT NULL default '',
  `messagehash` varchar(50) NOT NULL default '',
  `messagesource` mediumtext NOT NULL,
  `strip_tags` int(1) NOT NULL,
  `bounce` int(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`),
  KEY `ticketid` (`ticketid`,`timestamp`),
  KEY `timestamp` (`timestamp`),
  KEY `sourceid` (`sourceid`),
  KEY `userid` (`userid`),
  KEY `messagehash` (`messagehash`),
  FULLTEXT KEY `message` (`message`)
)   ENGINE=MyISAM 
";

$tables['ticket_message_draft'] = "
CREATE TABLE `ticket_message_draft` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ticketid` int(10) unsigned NOT NULL,
  `techid` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ticketid` (`ticketid`),
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['ticket_notes'] = "
CREATE TABLE `ticket_notes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ticketid` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `note` mediumtext NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `techid` (`techid`)
)   ENGINE=MyISAM 
";

$tables['ticket_participant'] = "
CREATE TABLE `ticket_participant` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL,
  `user_type` enum('user','tech') NOT NULL,
  `ticket` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(10) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`,`user_type`,`ticket`,`email`),
  KEY `ticket` (`ticket`)
)   ENGINE=MyISAM 
";

$tables['ticket_pri'] = "
CREATE TABLE `ticket_pri` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `displayorder` int(10) NOT NULL default '0',
  `color` varchar(250) NOT NULL default '',
  `name_language` mediumtext NOT NULL,
  `perm_all` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['ticket_pri_permissions'] = "
CREATE TABLE `ticket_pri_permissions` (
  `usergroup` int(10) unsigned NOT NULL,
  `priority` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`usergroup`,`priority`)
)   ENGINE=MyISAM 
";

$tables['ticket_rules_mail'] = "
CREATE TABLE `ticket_rules_mail` (
  `id` int(10) NOT NULL auto_increment,
  `auto_reply` int(1) NOT NULL default '0',
  `auto_new` int(1) NOT NULL default '0',
  `is_default` int(1) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `actions` mediumtext NOT NULL,
  `criteria` mediumtext NOT NULL,
  `accountid` int(10) NOT NULL default '0',
  `template_new_user` varchar(250) NOT NULL,
  `template_reply_user` varchar(250) NOT NULL,
  `template_reply_tech` varchar(250) NOT NULL,
  `template_permission` varchar(250) NOT NULL,
  `template_new_user_phrase_on` int(1) NOT NULL,
  `template_reply_user_phrase_on` int(1) NOT NULL,
  `template_reply_tech_phrase_on` int(1) NOT NULL,
  `template_permission_phrase_on` int(1) NOT NULL,
  `template_new_user_phrase` mediumtext NOT NULL,
  `template_reply_user_phrase` mediumtext NOT NULL,
  `template_reply_tech_phrase` mediumtext NOT NULL,
  `template_permission_phrase` mediumtext NOT NULL,
  `guest_handling_method` varchar(250) NOT NULL,
  `email_validate` varchar(250) NOT NULL,
  `tech_validate` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['ticket_rules_web'] = "
CREATE TABLE `ticket_rules_web` (
  `id` int(10) NOT NULL auto_increment,
  `auto_reply` int(1) NOT NULL default '0',
  `auto_new` int(1) NOT NULL default '0',
  `is_default` int(1) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `criteria` mediumtext NOT NULL,
  `actions` mediumtext NOT NULL,
  `accountid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['ticket_temp'] = "
CREATE TABLE `ticket_temp` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(250) NOT NULL,
  `ticket_data` mediumtext NOT NULL,
  `message_data` mediumtext NOT NULL,
  `extra_data` text NOT NULL,
  `validate_key` varchar(6) NOT NULL,
  `messagehash` varchar(32) NOT NULL,
  `timestamp_submitted` int(11) NOT NULL,
  `timestamp_reminder1` int(11) NOT NULL,
  `timestamp_reminder2` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['ticket_views'] = "
CREATE TABLE `ticket_views` (
  `id` int(10) NOT NULL auto_increment,
  `techid` int(10) NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `isglobal` int(1) NOT NULL default '0',
  `tickets` int(10) NOT NULL default '0',
  `description` mediumtext NOT NULL,
  `name` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['ticket_workflow'] = "
CREATE TABLE `ticket_workflow` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `displayorder` int(10) NOT NULL default '0',
  `next_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['tracking'] = "
CREATE TABLE `tracking` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL,
  `sessionid` varchar(32) NOT NULL,
  `external_id` varchar(32) NOT NULL,
  `timestamp_created` int(11) NOT NULL,
  `timestamp_visit` int(11) NOT NULL,
  `timestamp_last` int(11) NOT NULL,
  `lastlogid` int(10) unsigned NOT NULL,
  `visitlogid` int(11) NOT NULL default '0',
  `logcount` int(10) unsigned NOT NULL,
  `chatinit_timestamp` int(10) unsigned NOT NULL,
  `chatinit_techid` int(10) unsigned NOT NULL,
  `chatinit_message` varchar(255) NOT NULL,
  `chatinit_accepted` tinyint(1) NOT NULL,
  `chatinit_ignored` tinyint(1) NOT NULL,
  `chatinit_disable` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`,`sessionid`),
  KEY `external_id` (`external_id`)
)   ENGINE=MyISAM 
";

$tables['tracking_loc'] = "
CREATE TABLE `tracking_loc` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `locgroup` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `pattern` text NOT NULL,
  `infoclass` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['tracking_loc_group'] = "
CREATE TABLE `tracking_loc_group` (
  `sysname` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`sysname`)
)   ENGINE=MyISAM 
";

$tables['tracking_log'] = "
CREATE TABLE `tracking_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `trackid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `sessionid` varchar(32) NOT NULL,
  `locid` int(10) unsigned NOT NULL,
  `ipaddr` varchar(50) NOT NULL,
  `useragent` text NOT NULL,
  `pagetitle` text NOT NULL,
  `pageinfo` text NOT NULL,
  `data` text NOT NULL,
  `url` text NOT NULL,
  `ref` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  `is_new_visit` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `trackid` (`trackid`),
  KEY `userid` (`userid`)
)   ENGINE=MyISAM 
";

$tables['tracking_search'] = "
CREATE TABLE `tracking_search` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `trackid` int(10) unsigned NOT NULL,
  `logid` int(10) unsigned NOT NULL,
  `botname` varchar(255) NOT NULL,
  `query` text NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['trouble'] = "
CREATE TABLE `trouble` (
  `id` int(10) NOT NULL auto_increment,
  `description` mediumtext NOT NULL,
  `displayorder` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `timestamp_created` int(10) NOT NULL default '0',
  `timestamp_updated` int(10) NOT NULL default '0',
  `publish` int(1) NOT NULL default '0',
  `auth` varchar(50) NOT NULL default '',
  `content` mediumtext NOT NULL,
  `end_good` mediumtext NOT NULL,
  `end_bad` mediumtext NOT NULL,
  `name` varchar(250) NOT NULL default '',
  `choices_title` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['trouble_comments'] = "
CREATE TABLE `trouble_comments` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `troubleid` int(10) NOT NULL default '0',
  `comments` mediumtext NOT NULL,
  `session` varchar(32) NOT NULL default '',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `troubleid` (`troubleid`),
  KEY `userid` (`userid`)
)   ENGINE=MyISAM 
";

$tables['trouble_path_log'] = "
CREATE TABLE `trouble_path_log` (
  `id` int(11) NOT NULL auto_increment,
  `trouble_session_id` int(11) NOT NULL,
  `trouble_question_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['trouble_permissions'] = "
CREATE TABLE `trouble_permissions` (
  `troubleid` int(10) NOT NULL default '0',
  `groupid` int(10) NOT NULL default '0',
  UNIQUE KEY `groupid` (`groupid`,`troubleid`)
)   ENGINE=MyISAM 
";

$tables['trouble_questions'] = "
CREATE TABLE `trouble_questions` (
  `id` int(10) NOT NULL auto_increment,
  `question` mediumtext NOT NULL,
  `troubleid` int(10) NOT NULL default '0',
  `parent` int(10) NOT NULL default '0',
  `answer` mediumtext NOT NULL,
  `end` int(1) NOT NULL default '0',
  `title` mediumtext NOT NULL,
  `success` int(1) NOT NULL default '0',
  `failure` int(1) NOT NULL default '0',
  `choices_title` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `troubleid` (`troubleid`)
)   ENGINE=MyISAM 
";

$tables['trouble_rating'] = "
CREATE TABLE `trouble_rating` (
  `id` int(10) NOT NULL auto_increment,
  `troubleid` int(10) NOT NULL default '0',
  `rating` int(1) NOT NULL default '0',
  `userid` int(10) NOT NULL default '0',
  `sessionid` varchar(32) NOT NULL default '',
  `ipaddress` varchar(20) NOT NULL default '',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `troubleid` (`troubleid`)
)   ENGINE=MyISAM 
";

$tables['trouble_session'] = "
CREATE TABLE `trouble_session` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `trouble_id` int(11) NOT NULL,
  `started_at` int(11) NOT NULL,
  `ended_at` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user'] = "
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `btechid` int(10) unsigned NOT NULL,
  `username` varchar(250) NOT NULL default '',
  `validate_key` varchar(6) NOT NULL default '0',
  `awaiting_register_validate_user` int(1) NOT NULL default '0',
  `last_autosession` int(10) unsigned NOT NULL,
  `date_registered` int(10) NOT NULL default '0',
  `last_activity` int(10) unsigned NOT NULL,
  `language` int(10) NOT NULL default '0',
  `password_cookie` varchar(8) NOT NULL default '',
  `password_url` varchar(8) NOT NULL default '',
  `autoresponds` int(1) NOT NULL default '0',
  `disabled` int(1) NOT NULL default '0',
  `awaiting_register_validate_tech` int(1) NOT NULL default '0',
  `expire_tickets` int(10) NOT NULL default '0',
  `expire_date` int(10) NOT NULL default '0',
  `disabled_reason` varchar(250) default NULL,
  `timezone` varchar(4) NOT NULL default '',
  `name` varchar(250) NOT NULL default '',
  `timezone_dst` int(1) NOT NULL default '0',
  `dst_auto_adjust` int(1) NOT NULL default '0',
  `style` int(10) NOT NULL default '0',
  `password_change_timestamp` int(10) NOT NULL default '0',
  `password_change_key` varchar(250) NOT NULL default '',
  `user_ticketlist_settings` mediumtext NOT NULL,
  `default_company` int(10) unsigned NOT NULL,
  `default_emailid` int(10) unsigned default NULL,
  `last_cache_update` int(11) default NULL,
  `reg_from` varchar(60) NOT NULL,
  `has_validated` tinyint(1) NOT NULL default '0',
  `billing_credits` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`),
  KEY `awaiting_manual_validation` (`awaiting_register_validate_tech`),
  KEY `name` (`name`),
  KEY `btechid` (`btechid`),
  KEY `username` (`username`)
)   ENGINE=MyISAM 
";

$tables['user_bill'] = "
CREATE TABLE `user_bill` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `ticketid` int(10) NOT NULL default '0',
  `timecharge` int(10) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `comments` mediumtext NOT NULL,
  `charge` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `ticketid` (`ticketid`,`userid`),
  KEY `userid` (`userid`)
)   ENGINE=MyISAM 
";

$tables['user_company'] = "
CREATE TABLE `user_company` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `cc_emails` text NOT NULL,
  `p_ticket_company_viewothers` tinyint(1) NOT NULL default '0',
  `p_ticket_company_addself` tinyint(1) NOT NULL default '0',
  `p_company_field_view` tinyint(1) NOT NULL default '0',
  `p_company_field_edit` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_company2group'] = "
CREATE TABLE `user_company2group` (
  `companyid` int(10) unsigned NOT NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`companyid`,`groupid`)
)   ENGINE=MyISAM 
";

$tables['user_company_def'] = "
CREATE TABLE `user_company_def` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) default NULL,
  `display_name` mediumtext NOT NULL,
  `description` mediumtext,
  `formtype` varchar(250) NOT NULL default 'input',
  `default_value` varchar(250) default NULL,
  `parsed_default_value` varchar(250) default NULL,
  `data` mediumtext NOT NULL,
  `maxoptions` smallint(4) NOT NULL default '0',
  `minoptions` smallint(4) NOT NULL default '0',
  `maxlength` smallint(6) NOT NULL default '0',
  `minlength` smallint(6) NOT NULL default '0',
  `regex` varchar(250) default NULL,
  `error_message` varchar(250) default NULL,
  `required` int(1) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `multiselect` int(1) NOT NULL default '0',
  `display_name_language` mediumtext NOT NULL,
  `description_language` mediumtext NOT NULL,
  `error_language` mediumtext NOT NULL,
  `php_default_value` mediumtext NOT NULL,
  `perm_user_view` enum('user','role','none') NOT NULL default 'user',
  `perm_user_edit` enum('user','role','none') NOT NULL default 'user',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_company_role'] = "
CREATE TABLE `user_company_role` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `overrides` text NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_def'] = "
CREATE TABLE `user_def` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) default NULL,
  `display_name` mediumtext NOT NULL,
  `description` mediumtext,
  `formtype` varchar(250) NOT NULL default 'input',
  `default_value` varchar(250) default NULL,
  `parsed_default_value` varchar(250) default NULL,
  `data` mediumtext NOT NULL,
  `maxoptions` smallint(4) NOT NULL default '0',
  `minoptions` smallint(4) NOT NULL default '0',
  `maxlength` smallint(6) NOT NULL default '0',
  `minlength` smallint(6) NOT NULL default '0',
  `regex` varchar(250) default NULL,
  `error_message` varchar(250) default NULL,
  `required` int(1) NOT NULL default '0',
  `displayorder` int(10) NOT NULL default '0',
  `multiselect` int(1) NOT NULL default '0',
  `display_name_language` mediumtext NOT NULL,
  `description_language` mediumtext NOT NULL,
  `error_language` mediumtext NOT NULL,
  `php_default_value` mediumtext NOT NULL,
  `show_on_search` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_deskpro'] = "
CREATE TABLE `user_deskpro` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(15) NOT NULL,
  `password_change_key` varchar(8) NOT NULL,
  `password_change_timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_email'] = "
CREATE TABLE `user_email` (
  `email` varchar(250) NOT NULL default '',
  `userid` int(10) NOT NULL default '0',
  `validated` int(1) NOT NULL default '0',
  `authcode` varchar(20) NOT NULL default '',
  `timestamp` int(10) NOT NULL default '0',
  `default_company` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL auto_increment,
  `mapid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`,`mapid`),
  KEY `userid` (`userid`),
  KEY `mapid` (`mapid`)
)   ENGINE=MyISAM 
";

$tables['user_groups'] = "
CREATE TABLE `user_groups` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `is_system` tinyint(1) NOT NULL default '0',
  `system_name` varchar(50) NOT NULL,
  `p_kb` tinyint(1) NOT NULL default '0',
  `p_kb_new` tinyint(1) NOT NULL default '0',
  `p_kb_subscribe` tinyint(1) NOT NULL default '0',
  `p_kb_comment` tinyint(1) NOT NULL default '1',
  `p_kb_rate` tinyint(1) NOT NULL default '1',
  `p_dl` tinyint(1) NOT NULL default '0',
  `p_trouble` tinyint(1) NOT NULL default '0',
  `p_trouble_rate` tinyint(1) NOT NULL default '0',
  `p_ticket` tinyint(1) NOT NULL default '0',
  `p_ticket_new` tinyint(1) NOT NULL default '0',
  `p_ticket_new_email` tinyint(1) NOT NULL default '0',
  `p_ticket_rate` tinyint(1) NOT NULL default '0',
  `p_ticket_reopen` tinyint(1) NOT NULL default '0',
  `p_chat` tinyint(1) NOT NULL,
  `p_ideas` tinyint(1) NOT NULL default '0',
  `p_ideas_vote` tinyint(1) NOT NULL default '0',
  `p_ideas_new` tinyint(1) NOT NULL default '0',
  `p_ideas_new_visible` tinyint(1) NOT NULL default '0',
  `p_ideas_comment_new` tinyint(1) NOT NULL default '0',
  `p_ideas_comment_view` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_help'] = "
CREATE TABLE `user_help` (
  `category` varchar(250) NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  `displayorder` int(10) NOT NULL default '0',
  `is_custom` int(1) NOT NULL default '0',
  PRIMARY KEY  (`name`)
)   ENGINE=MyISAM 
";

$tables['user_help_cats'] = "
CREATE TABLE `user_help_cats` (
  `displayorder` int(10) NOT NULL default '0',
  `is_custom` int(1) NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`name`)
)   ENGINE=MyISAM 
";

$tables['user_help_cats_entries'] = "
CREATE TABLE `user_help_cats_entries` (
  `catname` varchar(250) NOT NULL default '',
  `language` int(10) NOT NULL default '0',
  `entry` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`language`,`catname`)
)   ENGINE=MyISAM 
";

$tables['user_help_entries'] = "
CREATE TABLE `user_help_entries` (
  `language` int(10) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `helpentry` mediumtext NOT NULL,
  `helpname` varchar(250) NOT NULL default '',
  `changed` int(1) NOT NULL default '0',
  `backup` mediumtext NOT NULL,
  PRIMARY KEY  (`language`,`helpname`)
)   ENGINE=MyISAM 
";

$tables['user_idea_categories'] = "
CREATE TABLE `user_idea_categories` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default NULL,
  `title` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `display_order_idx` (`display_order`),
  KEY `parent_id_idx` (`parent_id`)
)   ENGINE=MyISAM 
";

$tables['user_idea_comments'] = "
CREATE TABLE `user_idea_comments` (
  `id` int(11) NOT NULL auto_increment,
  `idea_id` int(11) NOT NULL,
  `user_id` int(11) default NULL,
  `tech_id` int(11) default NULL,
  `message` text NOT NULL,
  `user_ip` varchar(255) default NULL,
  `user_hostname` varchar(255) default NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idea_id_idx` (`idea_id`),
  KEY `user_id_idx` (`user_id`),
  KEY `created_at_idx` (`created_at`)
)   ENGINE=MyISAM 
";

$tables['user_idea_votes'] = "
CREATE TABLE `user_idea_votes` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `tracking_id` int(11) default NULL,
  `idea_id` int(11) NOT NULL,
  `votes` int(11) NOT NULL default '0',
  `is_returned` tinyint(1) NOT NULL default '0',
  `user_ip` varchar(255) default NULL,
  `user_hostname` varchar(255) default NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idea_id_user_id_tracking_id_unqidx_idx` (`idea_id`,`user_id`,`tracking_id`),
  KEY `is_returned_idx` (`is_returned`),
  KEY `idea_id_idx` (`idea_id`),
  KEY `user_id_idx` (`user_id`),
  KEY `tracking_id_idx` (`tracking_id`),
  KEY `created_at_idx` (`created_at`)
)   ENGINE=MyISAM 
";

$tables['user_ideas'] = "
CREATE TABLE `user_ideas` (
  `id` int(11) NOT NULL auto_increment,
  `category_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `tracking_id` int(11) default NULL,
  `user_name` varchar(255) default NULL,
  `num_votes` int(11) NOT NULL default '0',
  `num_comments` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL default 'new',
  `completion_status` varchar(255) default NULL,
  `is_hidden` tinyint(1) NOT NULL default '0',
  `is_updated_notification` tinyint(1) NOT NULL default '0',
  `user_ip` varchar(255) default NULL,
  `user_hostname` varchar(255) default NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id_idx` (`user_id`),
  KEY `created_at_idx` (`created_at`),
  KEY `status_idx` (`status`),
  KEY `is_hidden_idx` (`is_hidden`),
  KEY `is_updated_notification_idx` (`is_updated_notification`),
  KEY `num_votes_idx` (`num_votes`),
  KEY `category_id_idx` (`category_id`)
)   ENGINE=MyISAM 
";

$tables['user_map'] = "
CREATE TABLE `user_map` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `localid` int(10) unsigned NOT NULL,
  `remoteid` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `sourceid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `localid` (`localid`,`remoteid`)
)   ENGINE=MyISAM 
";

$tables['user_map_validate'] = "
CREATE TABLE `user_map_validate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `localid` int(10) unsigned NOT NULL,
  `remoteid` varchar(255) NOT NULL,
  `sourceid` int(10) unsigned NOT NULL,
  `auth` varchar(8) NOT NULL,
  `timestamp_requested` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_member_company'] = "
CREATE TABLE `user_member_company` (
  `user` int(10) unsigned NOT NULL,
  `company` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`user`,`company`)
)   ENGINE=MyISAM 
";

$tables['user_member_company_role'] = "
CREATE TABLE `user_member_company_role` (
  `user` int(10) unsigned NOT NULL,
  `role` int(10) unsigned NOT NULL,
  `company` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`user`,`role`,`company`)
)   ENGINE=MyISAM 
";

$tables['user_member_groups'] = "
CREATE TABLE `user_member_groups` (
  `user` int(10) unsigned NOT NULL,
  `usergroup` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`user`,`usergroup`)
)   ENGINE=MyISAM 
";

$tables['user_notes'] = "
CREATE TABLE `user_notes` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) NOT NULL default '0',
  `techid` int(10) NOT NULL default '0',
  `note` mediumtext NOT NULL,
  `timestamp` int(10) NOT NULL default '0',
  `global` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`,`techid`)
)   ENGINE=MyISAM 
";

$tables['user_plan_subscriptions'] = "
CREATE TABLE `user_plan_subscriptions` (
  `id` int(11) NOT NULL auto_increment,
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `expire_at` int(11) default NULL,
  `is_expired` tinyint(1) default '0',
  `log` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `plan_id_user_id_unqidx_idx` (`plan_id`,`user_id`),
  KEY `user_id_idx` (`user_id`),
  KEY `expire_at_idx` (`expire_at`),
  KEY `is_expired_idx` (`is_expired`),
  KEY `plan_id_idx` (`plan_id`)
)   ENGINE=MyISAM 
";

$tables['user_plans'] = "
CREATE TABLE `user_plans` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(12,2) NOT NULL default '0.00',
  `duration` int(11) NOT NULL default '0',
  `actions` text NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_rules'] = "
CREATE TABLE `user_rules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `criteria` text NOT NULL,
  `actions` text NOT NULL,
  `run_web` tinyint(1) NOT NULL default '0',
  `run_order` int(11) NOT NULL default '0',
  `link_company` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_session'] = "
CREATE TABLE `user_session` (
  `sessionid` varchar(32) NOT NULL default '',
  `userid` int(10) unsigned NOT NULL default '0',
  `useragent` varchar(250) NOT NULL default '',
  `lastactivity` int(10) unsigned NOT NULL default '0',
  `language` int(10) NOT NULL default '0',
  `host` varchar(15) NOT NULL default '',
  `path` varchar(250) NOT NULL default '',
  `pagetype` varchar(250) NOT NULL default '',
  `pagevalue` varchar(250) NOT NULL default '',
  `style` int(10) NOT NULL default '0',
  `extra` mediumtext NOT NULL,
  PRIMARY KEY  (`sessionid`),
  KEY `userid` (`userid`)
)   ENGINE=MyISAM 
";

$tables['user_source'] = "
CREATE TABLE `user_source` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `module` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL default '0',
  `runorder` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `user_title` varchar(255) NOT NULL,
  `user_description` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `config` text NOT NULL,
  PRIMARY KEY  (`id`)
)   ENGINE=MyISAM 
";

$tables['user_token'] = "
CREATE TABLE `user_token` (
  `id` int(10) NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL,
  `sessionid` varchar(32) NOT NULL default '',
  `timestamp` int(10) NOT NULL default '0',
  `token_type` varchar(250) NOT NULL default '',
  `token_value` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `timestamp` (`timestamp`)
)   ENGINE=MyISAM 
";

?>