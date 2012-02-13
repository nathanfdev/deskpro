<?php

error_reporting(E_ALL & ~E_NOTICE & ~8192);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id$
// +-------------------------------------------------------------+
// | File Details:
// | - Upgrade to 3.4.1
// +-------------------------------------------------------------+

/*************************************
* UPGRADE CLASS
*************************************/

class upgrade_3040101 extends upgrade_base_v3 {

	var $version = '3.4.1';

	var $version_number = 3040101;

	var $pages = array(
		array('Misc', 'options.gif'),
		array('Billing Plugin', 'options.gif'),
	);

	/***************************************************
	* Misc chnges
	***************************************************/

	function step1() {

	    global $db;

	    $this->start('Add display_order to report_relations table');
	    $db->query("ALTER TABLE  `report_relations` ADD  `displayorder` INT NOT NULL");
	    $this->yes();

	    $this->start('Make nodisplay on ticket table free');
	    $db->query("ALTER TABLE  `ticket` CHANGE  `nodisplay`  `nodisplay` VARCHAR( 50 ) NULL DEFAULT NULL");
	    $this->yes();
	}

	/***************************************************
	* Billing Plugin
	***************************************************/

	function step2() {

		global $db;

		$this->start('Adding billing_credits to user table');
		$db->query("ALTER TABLE  `user` ADD  `billing_credits` DECIMAL( 12, 2 ) NOT NULL DEFAULT  '0.00';");
		$this->yes();

		$this->start('Creating billing_rules table');
		$db->query("CREATE TABLE billing_rules (id INT NOT NULL AUTO_INCREMENT, parent_id INT, run_order INT DEFAULT 0 NOT NULL, criteria TEXT NOT NULL, cost_ticket DECIMAL(12,2) DEFAULT 0 NOT NULL, cost_time DECIMAL(12,2) DEFAULT 0 NOT NULL, is_set_amount TINYINT(1) DEFAULT '0' NOT NULL, is_last TINYINT(1) DEFAULT '1' NOT NULL, cancel_ids TEXT NOT NULL, admin_comment TEXT NOT NULL, user_comment TEXT NOT NULL, INDEX parent_id_idx (parent_id), PRIMARY KEY(id)) ENGINE = MyISAM");
		$this->yes();

		$this->start('Creating billing_order table');
		$db->query("CREATE TABLE billing_order (id INT NOT NULL AUTO_INCREMENT, ref VARCHAR(50) NOT NULL, user_id INT NOT NULL, ticket_id INT, summary VARCHAR(255) DEFAULT '' NOT NULL, amount DECIMAL(12,2) DEFAULT 0 NOT NULL, created_at INT NOT NULL, finished_at INT, cart TEXT NOT NULL, status VARCHAR(255) DEFAULT 'pending' NOT NULL, process_status VARCHAR(255) DEFAULT 'none' NOT NULL, log TEXT NOT NULL, notes TEXT NOT NULL, is_hidden TINYINT(1) DEFAULT '0', UNIQUE INDEX ref_unqidx_idx (ref), INDEX user_id_idx (user_id), INDEX ticket_id_idx (ticket_id), INDEX created_at_idx (created_at), INDEX finished_at_idx (finished_at), INDEX status_idx (status), INDEX is_hidden_idx (is_hidden), PRIMARY KEY(id)) ENGINE = MyISAM");
		$this->yes();

		$this->start('Creating billing_transaction table');
		$db->query("CREATE TABLE billing_transaction (id INT NOT NULL AUTO_INCREMENT, user_id INT NOT NULL, billing_order_id INT NOT NULL, transaction_id VARCHAR(255) DEFAULT '' NOT NULL, payment_gateway_sysname VARCHAR(255) DEFAULT '' NOT NULL, status VARCHAR(255) DEFAULT 'pending' NOT NULL, gateway_status VARCHAR(255) DEFAULT '' NOT NULL, amount DECIMAL(12,2) DEFAULT 0 NOT NULL, currency VARCHAR(255) DEFAULT 'USD' NOT NULL, created_at INT NOT NULL, finished_at INT, details TEXT NOT NULL, request TEXT NOT NULL, response TEXT NOT NULL, INDEX user_id_idx (user_id), INDEX created_at_idx (created_at), INDEX finished_at_idx (finished_at), INDEX status_idx (status), INDEX billing_order_id_idx (billing_order_id), PRIMARY KEY(id)) ENGINE = MyISAM");
		$this->yes();

		$this->start('Creating payment_gateways table');
		$db->query("CREATE TABLE payment_gateways (sysname VARCHAR(50) NOT NULL, classname VARCHAR(50) NOT NULL, settings TEXT NOT NULL, is_active TINYINT(1) DEFAULT '0' NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(sysname)) ENGINE = MyISAM");
		$this->yes();

		$this->start('Creating payment_gateway_logs table');
		$db->query("CREATE TABLE payment_gateway_logs (id INT NOT NULL AUTO_INCREMENT, sysname VARCHAR(50) NOT NULL, summary VARCHAR(255) DEFAULT '' NOT NULL, details TEXT NOT NULL, created_at INT NOT NULL, is_error_type TINYINT(1) DEFAULT '0' NOT NULL, INDEX sysname_idx (sysname), INDEX is_error_type_idx (is_error_type), PRIMARY KEY(id)) ENGINE = MyISAM");
		$this->yes();

		$this->start('Creating user_plans table');
		$db->query("CREATE TABLE user_plans (id INT NOT NULL AUTO_INCREMENT, title TEXT NOT NULL, description TEXT NOT NULL, cost DECIMAL(12,2) DEFAULT 0 NOT NULL, duration INT DEFAULT 0 NOT NULL, actions TEXT NOT NULL, PRIMARY KEY(id)) ENGINE = MyISAM");
		$this->yes();

		$this->start('Creating user_plan_subscriptions');
		$db->query("CREATE TABLE user_plan_subscriptions (id INT NOT NULL AUTO_INCREMENT, plan_id INT NOT NULL, user_id INT NOT NULL, created_at INT NOT NULL, expire_at INT, is_expired TINYINT(1) DEFAULT '0', log TEXT NOT NULL, UNIQUE INDEX plan_id_user_id_unqidx_idx (plan_id, user_id), INDEX user_id_idx (user_id), INDEX expire_at_idx (expire_at), INDEX is_expired_idx (is_expired), INDEX plan_id_idx (plan_id), PRIMARY KEY(id)) ENGINE = MyISAM");
		$this->yes();;

		$this->start('Creating billing_credit_bundles table');
		$db->query("CREATE TABLE billing_credit_bundles (id INT NOT NULL AUTO_INCREMENT, require_credits DECIMAL(12,2) DEFAULT 10 NOT NULL, free_credits DECIMAL(12,2) DEFAULT 2 NOT NULL, PRIMARY KEY(id)) ENGINE = MyISAM");
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
$upgrade = new upgrade_3040101();

// do the header
$upgrade->header();

// run the next step
$upgrade->runStep($request->getNumber('step', 'request'));