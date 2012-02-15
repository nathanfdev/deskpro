<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.5.5
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3050502 extends upgrade_base_v3 {
	var $version = '3.5.5.1';

	var $version_number = 3050502;

	protected $has_remote_data = true;

	var $pages = array(
		array('Add message_triggers table', 'options.gif'),
	);

	/***************************************************
	* Misc chnges
	***************************************************/

	function step1() {
		$this->start('Create message_triggers table ...');
		global $db;

		$db->query("
			CREATE TABLE IF NOT EXISTS `message_triggers` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `trigger_newticket` tinyint(1) NOT NULL DEFAULT '0',
			  `trigger_newreply` tinyint(1) NOT NULL DEFAULT '0',
			  `criteria_message_exact_match` text NOT NULL,
			  `criteria_message_match_any` text NOT NULL,
			  `criteria_message_match_all` text NOT NULL,
			  `criteria_message_match_regex` text NOT NULL,
			  `criteria_category` int(10) NOT NULL DEFAULT '0',
			  `criteria_priority` int(10) NOT NULL DEFAULT '0',
			  `criteria_tech` int(10) NOT NULL DEFAULT '0',
			  `criteria_usergroup` varchar(250) NOT NULL,
			  `criteria_useremail` varchar(250) NOT NULL,
			  `actions_email_owner` int(1) NOT NULL DEFAULT '0',
			  `actions_pm_owner` int(1) NOT NULL DEFAULT '0',
			  `actions_email_techs` mediumtext NOT NULL,
			  `actions_pm_techs` mediumtext NOT NULL,
			  `actions_category` int(1) NOT NULL DEFAULT '0',
			  `actions_priority` int(1) NOT NULL DEFAULT '0',
			  `actions_tech` int(1) NOT NULL DEFAULT '0',
			  `actions_add_reply` text NOT NULL,
			  `actions_add_reply_from` text NOT NULL,
			  `criteria_workflow` int(1) NOT NULL DEFAULT '0',
			  `actions_workflow` int(1) NOT NULL DEFAULT '0',
			  `criteria_company` int(10) unsigned DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM
		");

		$this->yes();
	}
}

/***************************************************
* - RUN CLASS
***************************************************/

// check we are in correct location
install_check();

// display header
$header->build();

// create the installer
$upgrade = new upgrade_3050502();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getString('step', 'request'));