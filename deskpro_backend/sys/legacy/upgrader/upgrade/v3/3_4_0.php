<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.4.0
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3040001 extends upgrade_base_v3 {

	var $version = '3.4.0';

	var $version_number = 3040001;

	var $pages = array(
		array('Misc', 'options.gif'),
	);

	/***************************************************
	*
	***************************************************/

	function step1() {

	    global $db;

	    $this->start('Creating user notebook tables');
	    $db->query("
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
			) ENGINE=MyISAM
	    ");

	    $db->query("
	    	CREATE TABLE `notebook_page2ticket` (
			  `ticket_id` int(11) NOT NULL,
			  `notebook_page_id` int(11) NOT NULL,
			  PRIMARY KEY  (`ticket_id`,`notebook_page_id`)
			) ENGINE=MyISAM
	    ");

	    $db->query("
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
			) ENGINE=MyISAM
	    ");
	    $this->yes();

	    $this->start('Adding next_id to workflow table');
	    $db->query("ALTER TABLE `ticket_workflow` ADD `next_id` INT NOT NULL");
	    $this->yes();

	    $this->start('Adding old_auth to ticket_merge table');
	    $db->query("ALTER TABLE `ticket_merge` ADD `old_authcode` VARCHAR( 250 ) NOT NULL AFTER `old_ref`");
	    $this->yes();

	    $this->start('Adding display_name to tech table');
	    $db->query("ALTER TABLE `tech` ADD `display_name` VARCHAR( 255 ) NOT NULL AFTER `name`");
	    $db->query("ALTER TABLE `tech` ADD INDEX `display_name` ( `display_name` )");
	    $this->yes();

	    $this->start('Populating display_name in tech table');
	    $all_techs = $db->query_return_array("SELECT id, username, name FROM tech");
	   
		// TODO replace this with something
		foreach ($all_techs as $t) {
	    	// $db->query_update('tech', array('display_name' => Orb_Util::coalesce($t['name'], $t['username'])), "id = {$t['id']}");
	    }

	    $this->yes();

	    $this->start('Get rid of obsolete tbale tech_activity_log');
	    $db->query("DROP TABLE IF EXISTS `tech_activity_log` ");
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
$upgrade = new upgrade_3040001();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getNumber('step', 'request'));