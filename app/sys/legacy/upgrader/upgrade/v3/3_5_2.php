<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.5.2
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3050201 extends upgrade_base_v3 {
	var $version = '3.5.2';

	var $version_number = 3050201;

	var $pages = array(
		array('Misc Changes', 'options.gif'),
		array('Index Changes', 'options.gif'),
	);

	/***************************************************
	* Misc chnges
	***************************************************/

	function step1() {
		global $db;

		if (!$db->query_table_has_column('user_company', 'cc_emails')) {
			$this->start('Add cc_emails to user_company table');
			$db->query("ALTER TABLE  `user_company` ADD  `cc_emails` TEXT NOT NULL DEFAULT  '' AFTER  `description`");
			$this->yes();
		}

		if (!$db->query_table_exists('template_attachments')) {
			$this->start('Add template_attachments table');
			$db->query("
				CREATE TABLE IF NOT EXISTS `template_attachments` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `blobid` int(11) NOT NULL,
				  `filename` varchar(255) NOT NULL,
				  `extension` varchar(50) NOT NULL,
				  `filesize` int(11) NOT NULL DEFAULT '0',
				  `created_at` int(11) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM
			");
			$this->yes();
		}

		$this->start("Modify fields of chat_chat table");
		if (!$db->query_table_has_column('chat_chat', 'did_send_transcript')) {
			$db->query("ALTER TABLE  `chat_chat` ADD  `did_send_transcript` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
			$db->query("ALTER TABLE  `chat_chat` ADD  `did_create_user` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
			$db->query("ALTER TABLE  `chat_chat` CHANGE  `user_typing`  `user_typing` VARCHAR( 255 ) NOT NULL DEFAULT  '0'");
		}
		$this->yes();

		$this->start("Add fields to tracking_log");
		if (!$db->query_table_has_column('tracking_log', 'is_new_visit')) {
			$db->query("ALTER TABLE  `tracking_log` ADD  `is_new_visit` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
			$db->query("ALTER TABLE  `tracking` ADD  `visitlogid` INT NOT NULL DEFAULT  '0' AFTER  `lastlogid`");
		}
		$this->yes();
	}

	function step2() {
		global $db;
		
		$this->start('Recreating index on ticket_participants');
		$db->query("
			ALTER TABLE `ticket_participant` DROP INDEX  `user` ,
			ADD UNIQUE  `user` (  `user` ,  `user_type` ,  `ticket` ,  `email` )
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
$upgrade = new upgrade_3050201();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getNumber('step', 'request'));