<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

// | HEADER REPLACE
// +-------------------------------------------------------------+
// | $Id: database_factory.php 6675 2010-03-09 11:03:58Z chroder $
// +-------------------------------------------------------------+
// | File Details:
// | - Database factory
// +-------------------------------------------------------------+

/**
 * This file contains the database factory responsible
 * for including the correct database class, and initiating it.
 *
 * @package	DeskPRO
 */

/**
 * Factory for creating database abstraction layer.
 *
 * @access	public
 *
 * @return	DB_Abstract	A database object
 */
function &database_factory($type = 'pdomysql', $forcenew = false) {
	
	require_once(INC . 'classes/database/PdoMysql.php');
	return new DB_PdoMysql();

}

function init_doctrine() {
	global $db;
	if ($db) {
		try {
			Doctrine_Manager::connection($db->link_id, 'main');
		} catch (Exception $e) {}
	}
}





/**
 * For backwards compat., some places
 * still call it instead of using $db
 *
 * @return DB_Abstract
 */
function &database_object_factory() {
	return database_factory('pdomysql');
}

