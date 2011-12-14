<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.3.2
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3030201 extends upgrade_base_v3 {

	var $version = '3.3.2';

	var $version_number = 3030201;

	var $pages = array(
		array('Misc', 'options.gif'),
	);

	/***************************************************
	*
	***************************************************/

	function step1() {

	    global $db;

		$this->start('Add permission to delete chat transcripts');
		$row = $db->query_return("SELECT * FROM tech LIMIT 1");
		if (!isset($row['p_chat_del_logs'])) {
			$db->query("ALTER TABLE `tech` ADD `p_chat_del_logs` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `p_chat_global_canned`");
		}
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
$upgrade = new upgrade_3030201();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getNumber('step', 'request'));