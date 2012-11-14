#!/usr/bin/env php
<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Stand-alone script that is used in conjunction with procmail.php
 * to retry messages that failed to properly upload to the remote site.
 *
 * @package DeskPRO_Cloud
 */

if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

########################################################################
# About and Configuration
########################################################################
# This command reads files in the _failed_retry directory and processes
# them again.
#
# The files in the _failed_retry directory look something like this:
#     <dp:data>
#     {
#         "to_addr": "contact@test001.deskpro.com",
#         "to_mailbox": "contact",
#         "to_domain": "test001.deskpro.com",
#         "savepath": "/home/cloudmail/mailstore/2012-11-14/10-31-44-test001.deskpro.com.649819672.eml",
#         "is_retry": 1
#     }
#     </dp:data>
#     <dp:log>
#     (The previous log file)
#     </dp:log>
#
# The dp:data chunk is a json encoded array of information about the original message.
# it is decoded and and then the original procmail command is run again.

/**
 * The directory to store messages into before saving
 * to the target cloud site database.
 */
define('DP_CLOUD_RETRY_DIR', __DIR__ . '/mailstore/_failed_retry');

/**
 * The path to the procmail.php file.
 */
define('DP_CLOUD_PROCMAIL_PATH', __DIR__ . '/procmail.php');


########################################################################
# Do not edit
########################################################################

class DeskPRO_Cloud_ProcMailRetry
{
	/**
	 * @var string
	 */
	protected $exit_string = '';

	/**
	 * @var int
	 */
	protected $exit_code = 0;

	public static function exec()
	{
		new self();
	}

	private function __construct()
	{
		date_default_timezone_set('UTC');

		if (!is_dir(DP_CLOUD_RETRY_DIR)) {
			trigger_error("RETRY_DIR dir does not exist: " . DP_CLOUD_RETRY_DIR, E_USER_ERROR);
			exit(1);
		}

		$this->run();

		if ($this->exit_string) {
			echo $this->exit_string;
		}
		exit($this->exit_code);
	}

	####################################################################################################################

	public function run()
	{
		$dh = dir(DP_CLOUD_RETRY_DIR);

		$files = array();
		while (($f = $dh->read()) !== false) {
			if ($f == '.' || $f == '..') continue;

			$path = DP_CLOUD_RETRY_DIR . '/' . $f;
			if (is_dir($path)) {
				continue;
			}

			$files[] = $path;
		}

		$dh->close();

		if (!$files) {
			return;
		}

		foreach ($files as $f) {
			$this->processFile($f);
		}
	}

	public function processFile($file)
	{
		#------------------------------
		# Read and decode
		#------------------------------

		$content = file_get_contents($file);
		if (!$content) {
			throw new \RuntimeException("Retry file is empty: " . $file);
		}

		if (!unlink($file)) {
			throw new \RuntimeException("Could not unlink retry file: " . $file);
		}

		if (!preg_match('#<dp:data>(.*?)</dp:data>#', $content, $m)) {
			throw new \RuntimeException("Retry file has invalid data: " . $content);
		}

		$data = trim($m[1]);
		$data = json_decode($data, true);

		if (!$data) {
			throw new \RuntimeException("Retry file has invalid data: " . $content);
		}

		if (!is_file($data['savepath'])) {
			throw new \RuntimeException("Retry file has invalid savepath: " . print_r($data,true));
		}

		#------------------------------
		# Process
		#------------------------------

		$cmd = sprintf(
			"cat %s | %s %s retry %s",
			escapeshellarg($data['savepath']),
			escapeshellarg(DP_CLOUD_PROCMAIL_PATH),
			$data['to_addr'],
			$data['is_retry'] + 1
		);

		$ret = $out = null;
		exec($cmd, $out, $ret);

		if (!$out) {
			$out = array();
		}

		$out = implode("\n", $out);
	}
}

DeskPRO_Cloud_ProcMailRetry::exec();