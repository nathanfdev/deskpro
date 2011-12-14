<?php

// +-------------------------------------------------------------+
// | $Id: checks.php 6676 2010-03-09 11:04:24Z chroder $
// +-------------------------------------------------------------+
// | File Details:
// | - database functions
// +-------------------------------------------------------------+

error_reporting(E_ALL & ~E_NOTICE & ~8192);

define('MINVERSION', '5.2.0');
define('GOODVERSION', '5.2.0');
define('MAXVERSION', '6.0.0');

/*
	1006 : Cant create database
	1007 : Can't create database (exists)
*/

class checkDatabase extends DB_MySQL {

	var $messages;

	function checkDatabase($server, $user, $password, $database) {

		$this->host = $server;
		$this->database = $database;
		$this->user = $user;
		$this->password = $password;

		$this->error_halt = false;

	}

	function parentconnect() {

		parent::connect();

		// no error
		$errorno = $this->geterrno();

		if (!$errorno) {

			$version = $this->query_return("SELECT VERSION() AS v");
			$version = $version['v'];

			// Get rid of non version info (ie -nt)
			$version = preg_replace('#[^\.0-9]#', '', $version);

			if(!version_compare($version, '4.1', '>='))
			{
				$this->messages[] = "<span class=\"install_red\">" . DP_NAME . " requires at least MySQL version 4.1. You should upgrade your version to continue.</span>";
				return false;
			}

			else
				return true;

		// something wrong
		} else {

			/*************
			* Connection failed
			*************/

			if ($errorno == 2005 OR $errorno == 2003) {

				$this->messages[] = "<span class=\"install_red\">Could not connect to mySQL server at host : " . $this->host . "</span>";
				$this->messages[] = "Please check the DATABASE_HOST setting inside /includes/config.php is correct. It should be localhost if your mySQL server is on the same server as your web server. It should be an IP address if your mySQL server is a different server to your webserver.</span>";
				$this->messages[] = "<span class=\"install_red\"><u>MySQL Error</u>: $this->error</span>";
				return false;

			/*************
			* User / Password auth failed
			*************/

			} elseif ($errorno == 1044 OR $errorno == 1045) {

				$this->messages[] = "<span class=\"install_green\">Connected to mySQL server at $this->host</span>";
				$this->messages[] = "<span class=\"install_red\">Could not authenticate with username : $this->user and password : $this->password</span>";
				$this->messages[] = "<span class=\"install_red\"><u>MySQL Error</u>: $this->error</span>";
				return false;

			/*************
			* Database does not exists
			*************/

			} elseif ($errorno == 1049) {

				$this->messages[] = "<span class=\"install_red\">The database name specified does not exist. Please create the database.</span>";
				return false;

			// unknown error
			} else {

				$this->messages[] = "<span class=\"install_red\">There was a problem connecting to the mySQL Server</span>--";
				$this->messages[] = "Error : $this->error";
				$this->messages[] = "Errorno : $errorno";

			}
		}
	}
}


class installchecks {

	var $messages;
	var $fail;
	var $warning;
	var $html;
	var $message_html;

	function installchecks($message_html = true) {

		$this->message_html = $message_html;

		if ($message_html) {
			$this->phpversion();
			$this->mysql();
			$this->pcre();
			$this->xml();
			$this->check_gd();
			$this->check_datastore();
		} else {
			$this->phpversion();
			$this->mysql();
			$this->pcre();
			$this->xml();
			$this->check_datastore();
		}

		if ($message_html) {
			$this->html = array2list($this->messages, '', "style=\"padding-bottom:7px\"");
		} else {
			$this->html = implode("\n", $this->messages);
		}
	}

	function yes($message) {

		if ($this->message_html) {
			$this->messages[] = "<span class=\"install_green\">$message</span>";
		} else {
			$this->messages[] = "SUCCESS: $message";
		}
	}

	function warning($message, $extra='') {

		if ($this->message_html) {

			if ($extra) {
				if (!is_array($extra)) {
					$extra = array($extra);
				}
				$extra = array2list($extra);
			}

			$this->messages[] = "<span class=\"install_orange\"><strong>$message</strong></span>$extra";
		} else {
			$this->messages[] = "WARNING: $message";
		}

		$this->warning = 1;
	}

	function fail($message, $extra='') {

		if ($this->message_html) {

		if ($extra) {
			if (!is_array($extra)) {
				$extra = array($extra);
			}
			$extra = array2list($extra);
		}

			$this->messages[] = "<span class=\"install_red\"><strong>$message</strong></span>$extra";
		} else {
			$this->messages[] = "FAILURE: $message";
		}

		$this->fail = 1;
	}

	function phpversion() {

		$phpversion = phpversion();

		if (version_compare($phpversion, MINVERSION) == '-1') {
			$this->fail("PHP version $phpversion", DP_NAME . ' Requires ' . MINVERSION . ' of PHP. You must upgrade PHP.');
		} elseif (version_compare($phpversion, GOODVERSION) == '-1') {
			$this->warning("PHP version $phpversion", DP_NAME . " will run using this version of PHP, however jpgraph - the software used to create graphs requires " . GOODVERSION . ". If you wish to view graphs you will need to upgrade PHP. You can do this now or in the future.");
		} elseif (version_compare($phpversion, MAXVERSION) != '-1') {
			$this->warning("PHP version $phpversion may not yet be supported by " . DP_NAME . ". Please contact " . DP_NAME . " Support for clarification.");
		} else {
			$this->yes("PHP version $phpversion is compatible with " . DP_NAME . ".");
		}
	}

	function pcre() {

		if (function_exists('preg_replace')) {
			$this->yes('PHP is compiled with PCRE support');
		} else {
			$this->fail('PHP is not compiled with PCRE support', 'You must compile PHP with PCRE support for ' . DP_NAME . ' to function.');
		}
	}

	function xml() {

		if (function_exists('xml_parser_create')) {
			$this->yes('PHP is compiled with XML support');
		} else {
			$this->fail('PHP is not compiled with XML support', 'You must compile PHP with XML support for ' . DP_NAME . ' to function.');
		}
	}

	function mysql() {
	
		$is_pdo = class_exists('PDO');
		$is_pdo_mysql = extension_loaded('pdo_mysql');

	    if ($is_pdo AND $is_pdo_mysql) {
	        $this->yes("PHP is compiled with both PDO_MySQL support.");
	    } elseif (!$is_pdo AND $is_pdo) {
	        $this->fail("PHP is compiled with PDO support, but not the PDO_MySQL module.", 'You must compile PHP with PDO_MySQL for ' . DP_NAME . ' to function.');
	    } else {
	    	$this->fail("PHP is not compiled with PDO support.", 'You must compile PHP with PDO and PDO_MySQL for ' . DP_NAME . ' to function.');
	    }
	}

	function check_gd() {

		if (function_exists('gd_info')) {
			$this->yes("PHP is compiled with GD support");
		} else {

			$array[] = "GD support is required for " . DP_NAME . " to be able to generate graphs.";
			$array[] = "You will be able to use " . DP_NAME . " without GD support, and you will be able to compile GD support without having to reinstall " . DP_NAME . ".";
			$array[] = "To enable GD support you will not to recompile PHP on Linux, on Windows you will need to enable GD from inside php.ini.";

			$this->warning("PHP is not compiled with GD support", $array);
		}
	}

	function check_datastore() {

		if (!file_exists(DATASTORE) OR !is_dir(DATASTORE) OR !is_writable(DATASTORE)) {
			$this->fail("Can not write to datastore directory", 'Please make the ' . DATASTORE . ' directory writable, for example on linux type chmod -R 777 /path/to/datastore (755 will work on some systems)');
			return false;
		}

		// if we can't write to the graph folder, try and change permission
		if (!file_exists(DATASTORE . 'graphs') OR !is_dir(DATASTORE . 'graphs') OR !is_writable(DATASTORE . 'graphs')) {
			@chmod(DATASTORE . 'graphs', 777);
		}

		if (!file_exists(DATASTORE . 'graphs') OR !is_dir(DATASTORE . 'graphs') OR !is_writable(DATASTORE . 'graphs')) {
			$this->fail('Can not write to the datastore/graphs directory', 'Please make the ' . DATASTORE . 'graphs directory writable. for example on linux type chmod -R 777 /path/to/datastore/graphs (755 will work on some systems)');
			return false;
		}

		// if we can't write to the mail folder, try and change permission
		if (!file_exists(DATASTORE . 'mail') OR !is_dir(DATASTORE . 'mail') OR !is_writable(DATASTORE . 'mail')) {
			@chmod(DATASTORE . 'mail', 777);
		}

		if (!file_exists(DATASTORE . 'mail') OR !is_dir(DATASTORE . 'mail') OR !is_writable(DATASTORE . 'mail')) {
			$this->fail('Can not write to the datastore/mail directory', 'Please make the ' . DATASTORE . 'mail directory writable. for example on linux type chmod -R 777 /path/to/datastore/mail (755 will work on some systems)');
			return false;
		}

		$this->yes("Datastore directory is writable");
		return true;

	}
}

?>