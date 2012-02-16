<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.5.3
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3050401 extends upgrade_base_v3 {
	var $version = '3.5.4';

	var $version_number = 3050401;

	protected $has_remote_data = true;

	var $pages = array(
		array('No Database Changes', 'options.gif'),
	);

	/***************************************************
	* Misc chnges
	***************************************************/

	function step1() {
		$this->start('No changes ...');
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
$upgrade = new upgrade_3050401();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getString('step', 'request'));