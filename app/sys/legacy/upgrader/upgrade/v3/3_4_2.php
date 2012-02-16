<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.4.2
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3040201 extends upgrade_base_v3 {

	var $version = '3.4.2';

	var $version_number = 3040201;

	var $pages = array(
		array('Misc', 'options.gif'),
	);

	/***************************************************
	* Misc chnges
	***************************************************/

	function step1() {

	    global $db;

		// New installs for 342 had the wrong version number. They reported
		// version as 341 because of an old value in settings. So when they
		// upgrade, this step will be run but the db schema is already up to date.
		$res = $db->query_return('SHOW TABLES LIKE "trouble_session"');
		if ($res AND in_array('trouble_session', $res)) {
			$this->start('Skipping');
			$this->yes();
		} else {
			$this->start('Add trouble_session table');
			$db->query("
				CREATE TABLE `trouble_session` (
				  `id` int(11) NOT NULL auto_increment,
				  `user_id` int(11) NOT NULL,
				  `trouble_id` int(11) NOT NULL,
				  `started_at` int(11) NOT NULL,
				  `ended_at` int(11) NOT NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM
			");
			$this->yes();
	
			$this->start('Add trouble_path_log table');
			$db->query("
				CREATE TABLE `trouble_path_log` (
				  `id` int(11) NOT NULL auto_increment,
				  `trouble_session_id` int(11) NOT NULL,
				  `trouble_question_id` int(11) NOT NULL,
				  `created_at` int(11) NOT NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM
			");
			$this->yes();
	
			$this->start('Add cost_reply to billing_rules table');
			$db->query("ALTER TABLE  `billing_rules` ADD  `cost_reply` DECIMAL( 12, 2 ) NOT NULL DEFAULT  '0.00' AFTER  `cost_ticket`");
			$this->yes();
	
			$this->start('Add next_reply_billed to ticket table');
			$db->query("ALTER TABLE  `ticket` ADD  `next_reply_billed` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `pinned` ");
			$this->yes();
	
			$this->start('Add filepath to blobs table');
			$db->query("ALTER TABLE  `blobs` ADD  `filepath` TEXT NULL");
			$this->yes();
		}
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
$upgrade = new upgrade_3040201();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getNumber('step', 'request'));