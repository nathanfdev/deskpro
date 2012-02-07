<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.5.0
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3050001 extends upgrade_base_v3 {
	var $version = '3.5.0';

	var $version_number = 3050001;

	var $pages = array(
		array('New Permissions', 'options.gif'),
		array('New Tables', 'options.gif'),
		array('Misc', 'options.gif'),
	);

	/***************************************************
	* Misc chnges
	***************************************************/

	function step1() {
		global $db;
		
		$this->start('Add new feedback permissions to user_groups table');
		$db->query("
			ALTER TABLE  `user_groups` ADD  `p_feedback` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `p_feedback_vote` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `p_feedback_new` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `p_feedback_new_visible` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `p_feedback_comment_new` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `p_feedback_comment_view` TINYINT( 1 ) NOT NULL DEFAULT  '0'
		");
		$this->yes();
		
		$this->start('Setting default usergroup permissions');
		$db->query("UPDATE user_groups SET p_feedback = 1, p_feedback_comment_view = 1 WHERE id = 1");
		$db->query("UPDATE user_groups SET p_feedback = 1, p_feedback_vote = 1, p_feedback_new = 1, p_feedback_new_visible = 1, p_feedback_comment_new = 1, p_feedback_comment_view = 1 WHERE id = 2");
		$this->yes();
		
		$this->start('Add new feedback permissions to tech table');
		$db->query("
			ALTER TABLE  `tech` ADD  `p_feedback_edit` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `p_feedback_delete` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `p_feedback_comment` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `p_feedback_comment_delete` TINYINT( 1 ) NOT NULL DEFAULT  '0'
		");
		$this->yes();
		
		$this->start('Setting default tech permissions');
		$db->query("UPDATE tech SET p_feedback_edit = 1, p_feedback_delete = 1, p_feedback_comment = 1, p_feedback_comment_delete = 1");
		$this->yes();		
	}
	
	/***************************************************
	* New tables
	***************************************************/
	
	function step2() {
		global $db;
		
		$tables = array();
		$tables['user_feedback_categories'] = "
		CREATE TABLE `user_feedback_categories` (
		  `id` int(11) NOT NULL auto_increment,
		  `parent_id` int(11) default NULL,
		  `title` varchar(255) NOT NULL,
		  `display_order` int(11) NOT NULL default '0',
		  PRIMARY KEY (`id`),
		  KEY `display_order_idx` (`display_order`),
		  KEY `parent_id_idx` (`parent_id`)
		)   ENGINE=MyISAM 
		";
		
		$tables['user_feedback_comments'] = "CREATE TABLE user_feedback_comments (id INT NOT NULL AUTO_INCREMENT, feedback_id INT NOT NULL, user_id INT, tech_id INT, message TEXT NOT NULL, user_ip VARCHAR(255) DEFAULT NULL, user_hostname VARCHAR(255) DEFAULT NULL, created_at INT NOT NULL, INDEX feedback_id_idx (feedback_id), INDEX user_id_idx (user_id), INDEX created_at_idx (created_at), PRIMARY KEY(id)) ENGINE = MyISAM";
		
		$tables['user_feedback_votes'] = "CREATE TABLE user_feedback_votes (id INT NOT NULL AUTO_INCREMENT, user_id INT, tracking_id INT, feedback_id INT NOT NULL, votes INT DEFAULT 0 NOT NULL, is_returned TINYINT(1) DEFAULT '0' NOT NULL, user_ip VARCHAR(255) DEFAULT NULL, user_hostname VARCHAR(255) DEFAULT NULL, created_at INT NOT NULL, UNIQUE INDEX feedback_id_user_id_tracking_id_unqidx_idx (feedback_id, user_id, tracking_id), INDEX is_returned_idx (is_returned), INDEX feedback_id_idx (feedback_id), INDEX user_id_idx (user_id), INDEX tracking_id_idx (tracking_id), INDEX created_at_idx (created_at), PRIMARY KEY(id)) ENGINE = MyISAM";
		
		$tables['user_feedback'] = "CREATE TABLE user_feedback (id INT NOT NULL AUTO_INCREMENT, category_id INT, user_id INT, tracking_id INT, user_name VARCHAR(255), num_votes INT DEFAULT 0 NOT NULL, num_comments INT DEFAULT 0 NOT NULL, title VARCHAR(255) NOT NULL, message TEXT NOT NULL, status VARCHAR(255) DEFAULT 'new' NOT NULL, completion_status VARCHAR(255), is_hidden TINYINT(1) DEFAULT '0' NOT NULL, is_updated_notification TINYINT(1) DEFAULT '0' NOT NULL, user_ip VARCHAR(255) DEFAULT NULL, user_hostname VARCHAR(255) DEFAULT NULL, created_at INT NOT NULL, INDEX user_id_idx (user_id), INDEX created_at_idx (created_at), INDEX status_idx (status), INDEX is_hidden_idx (is_hidden), INDEX is_updated_notification_idx (is_updated_notification), INDEX num_votes_idx (num_votes), INDEX category_id_idx (category_id), PRIMARY KEY(id)) ENGINE = MyISAM";
		
		foreach ($tables as $tname => $tquery) {
			$this->start("Create table $tname");
			$db->query($tquery);
			$this->yes();
		}
	}
	
	/***************************************************
	* Misc
	***************************************************/
	
	function step3() {
		global $db;
		
		$this->start('Adding pref_afterreply_redirect to tech table');
		$db->query("ALTER TABLE  `tech` ADD  `pref_afterreply_redirect` VARCHAR( 20 ) NOT NULL DEFAULT  'search'");
		$this->yes();
		
		$this->start('Adding extra to search table');
		$db->query("ALTER TABLE  `search` ADD  `extra` TEXT NULL DEFAULT NULL");
		$this->yes();
		
		$this->start('Adding extra_data to ticket_tmp table');
		$db->query("ALTER TABLE  `ticket_temp` ADD  `extra_data` TEXT NOT NULL AFTER  `message_data`");
		$this->yes();
		
		$this->start('Adding admin_loginlockout_override to tech table');
		$db->query("ALTER TABLE  `tech` ADD  `admin_loginlockout_override` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
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
$upgrade = new upgrade_3050001();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getNumber('step', 'request'));