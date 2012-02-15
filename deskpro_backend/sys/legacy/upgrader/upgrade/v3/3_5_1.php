<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.5.1
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3050101 extends upgrade_base_v3 {
	var $version = '3.5.1';

	var $version_number = 3050101;

	var $pages = array(
		array('No Changes', 'options.gif'),
	);

	/***************************************************
	* Misc chnges
	***************************************************/

	function step1() {
		global $db;

		$this->start('No database changes needed');
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
$upgrade = new upgrade_3050101();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getNumber('step', 'request'));