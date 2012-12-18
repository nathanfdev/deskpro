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
 * Stand-alone script that is copied to a mail-server
 * and is used to accept incoming mail.
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
# This command saves a copy of the mail to the filesystem and then inserts the message into
# the correct cloud database.
#
# This command is PIPE'ed a raw email and should be called with a single argument
# being the original mailbox it was sent into (aka the cloud sites email address).
#
# For example, if you were to call this command manually to test:
#     cat mymailmessage.txt | procmail.php contact@test.deskpro.com
#
# Failed messages are put into a _failed_retry directory where the companion script procmail-retry.php
# is used to execute commands like:
#    cate retry-message.txt | procmail.php contact@test.deskpro.com retry 1
#
# If the message could not be inserted into the target database, a new file
# entry is created in DP_CLOUD_MAILSTORE/_failed whose contents is the full path to the
# email that failed.
#
# If we could not find a site for the message, a new file entry is created in
# DP_CLOUD_MAILSTORE/_unknown

/**
 * The directory to store messages into before saving
 * to the target cloud site database.
 */
define('DP_CLOUD_MAILSTORE', __DIR__ . '/mailstore');

/**
 * The web script to PUT the email to
 */
define('DP_CLOUD_SAVEMAIL_URL', 'http://{DOMAIN}/index.php?_sys=savemail&auth=dWso6yWsKDfZe5AJOFJYNTrkylLFHN&{PARAMS}');

/**
 * If set, then this IP address will be used. This means DNS is bypassed when sending
 * the result to the server.
 */
define('DP_CLOUD_SAVEMAIL_IP', null);

/**
 * Path to the mail log file
 */
define('DP_CLOUD_MAILLOG_PATH', __DIR__ . '/procmail.log');

/**
 * When a message in a request has at least this level, then log will be written
 */
define('DP_CLOUD_MAILLOG_LEVEL', 5);

/**
 * How many times to retry before giving up
 */
define('DP_CLOUD_RETRY_LIMIT', 3);

/**
 * Database details for the cloud database to
 * lookup site information.
 */
define('DP_CLOUD_DATABASE_HOST', 'localhost');
define('DP_CLOUD_DATABASE_USER', 'root');
define('DP_CLOUD_DATABASE_PASSWORD', '');
define('DP_CLOUD_DATABASE_NAME', '');
define('DP_CLOUD_DATABASE_MAXPACKET', 16777216);


########################################################################
# Set up postfix
########################################################################

/*

=== /etc/postfix/main.cf ===
	virtual_transport = cloudprocmail
	cloudprocmail_destination_recipient_limit=1
	virtual_mailbox_domains = regexp:/etc/postfix/virtual-domains
	virtual_mailbox_maps = regexp:/etc/postfix/virtual-mailboxes

=== /etc/postfix/virtual-domains ===
	/([a-zA-Z0-9\-]+)(\.deskpro\.com)$/   ACCEPT

=== /etc/postfix/virtual-mailboxes ===
	/@([a-zA-Z0-9\-]+)(\.deskpro\.com)$/   ACCEPT

=== /etc/postfix/virtual-mailboxes ===
	cloudprocmail unix  -       n       n       -       -       pipe flags=O user=cloudmail argv=/home/cloudmail/procmail.php ${original_recipient}

*/


########################################################################
# Do not edit
########################################################################

class DeskPRO_Cloud_ProcMail
{
	/**@#+ Standard log levels */
	const EMERG   = 0;
    const ALERT   = 1;
    const CRIT    = 2;
    const ERR     = 3;
    const WARN    = 4;
    const NOTICE  = 5;
    const INFO    = 6;
    const DEBUG   = 7;
    const STRICT   = 8;
	/**@#-*/

	/**
	 * @var string
	 */
	protected $to_addr;

	/**
	 * @var string
	 */
	protected $to_mailbox;

	/**
	 * @var string
	 */
	protected $to_domain;

	/**
	 * @var string
	 */
	protected $savedir;

	/**
	 * @var string
	 */
	protected $savepath;

	/**
	 * @var string
	 */
	protected $exit_string = '';

	/**
	 * @var int
	 */
	protected $exit_code = 0;

	/**
	 * @var array
	 */
	protected $log_messages = array();

	/**
	 * @var string
	 */
	protected $session_id;

	/**
	 * If this is a retry, and if so, the attempt number
	 * @var int
	 */
	protected $is_retry = 0;

	/**
	 * @var bool
	 */
	protected $is_old_corphelp = false;

	public static function exec()
	{
		new self();
	}

	private function __construct()
	{
		$this->session_id = uniqid();

		date_default_timezone_set('UTC');

		if (!is_writable(DP_CLOUD_MAILSTORE)) {
			trigger_error("MAILSTORE is not writable: " . DP_CLOUD_MAILSTORE, E_USER_ERROR);
			exit(1);
		}

		$that = $this;
		register_shutdown_function(function() use ($that) {
			$that->flushLogIfLevel(DP_CLOUD_MAILLOG_LEVEL);
		});

		$this->run();

		if ($this->exit_string) {
			echo $this->exit_string;
		}
		exit($this->exit_code);
	}

	####################################################################################################################

	public function run()
	{
		if (isset($_SERVER['argv'][1])) {
			$this->to_addr = $_SERVER['argv'][1];
			list($this->to_mailbox, $this->to_domain) = explode('@', $this->to_addr, 2);
		} else {
			$this->to_addr = $this->to_mailbox = $this->to_domain = 'UNKNOWN';
		}

		if ($this->to_domain == 'corphelp.com' && strpos($this->to_mailbox, '.') !== false) {
			$this->log(sprintf("corphelp_to(%s)", $this->to_addr));

			$this->is_old_corphelp = $this->to_addr;

			list ($name, $site_name) = explode('.', $this->to_mailbox);
			$this->to_domain  = $site_name . '.deskpro.com';
			$this->to_mailbox = $name;
			$this->to_addr    = $this->to_mailbox . '@' . $this->to_domain;
		}

		$this->log(sprintf("to_mailbox(%s)   to_domain(%s)", $this->to_mailbox, $this->to_domain));

		if (isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == 'retry' && isset($_SERVER['argv'][3])) {
			$this->is_retry = (int)$_SERVER['argv'][3];
			$this->log(sprintf("is_retry(%s)", $this->is_retry));
		}

		$this->saveToFilesystem();

		if ($this->is_old_corphelp) {
			$arg = escapeshellarg('s/' . preg_quote($this->is_old_corphelp, '/') . '/' . preg_quote($this->to_addr, '/') . '/g');
			exec(sprintf('sed -i %s %s', $arg, $this->savepath));
		}

		$this->saveToTarget();
	}

	####################################################################################################################

	/**
	 * Saves mail from STDIN to the mail directory
	 */
	protected function saveToFilesystem()
	{
		$this->savedir = DP_CLOUD_MAILSTORE . '/' . date('Y-m-d');

		$this->log(sprintf("saveToFilesystem: savedir(%s)", $this->savedir));

		if (!is_dir($this->savedir)) {
			if (!mkdir($this->savedir, 0755, true)) {
				$this->log("saveToFilesystem: Could not create MAILSTORE_DIR", self::ERR);
				trigger_error("Could not create MAILSTORE_DIR: $this->savedir", E_USER_ERROR);
				exit(1);
			}
		}

		$this->savepath = $this->savedir . '/' . date('H-i-s') . '-' . $this->to_domain . '.' . mt_rand(100000000,999999999) . '.eml';
		$this->log(sprintf("saveToFilesystem: savepath(%s)", $this->savepath));

		$fp = fopen($this->savepath, 'w');

		while (!feof(STDIN)) {
			fwrite($fp, fread(STDIN, 2048));
		}

		fclose($fp);
	}

	####################################################################################################################

	/**
	 * Saves mail to the target database
	 */
	protected function saveToTarget()
	{
		if ($this->to_domain == "UNKNOWN") {
			$this->log("saveToTarget: to_domain unknown, do not know where to route message");
			$this->markUnknown();
			return;
		}

		try {
			try {
				$cloudsite = $this->findCloudSite();
			} catch (\Exception $e) {
				$this->log("saveToTarget: exception connecting to cloud site: {$e->getMessage()}");
				$this->markFailed();
				return;
			}

			if (!$cloudsite) {
				$this->log("saveToTarget: could not find cloud site, do not know where to route message");
				$this->markUnknown();
				return;
			}

			// If the site is cancelled or demo expired, bounce
			if ($cloudsite['is_cancelled'] || ($cloudsite['is_demo'] && $cloudsite['date_demo_expire'] < time())) {
				$this->log("savetoTarget: cloud site is cancelled or demo expired, message bounce");
				$this->markUnknown();
				$this->exit_string = "Site is cancelled or demo expired";
				$this->exit_code = 2;
				return;
			}

			$this->uploadToSite($cloudsite);
		} catch (\Exception $e) {

			$this->log("savetoTarget: exception: " . $e->getMessage());

			// If the DB failed we can still upload to remote site,
			// The site check is just to make sure theres a real site. But
			// if DB is down, we still want to store the record so if it comes back
			// it'll be processed.
			$this->uploadToSite();

			throw $e; // throw up to be logged
		}
	}

	/**
	 * @return array
	 */
	protected function findCloudSite()
	{
		$pdo = $this->getCloudDb();

		$q = $pdo->prepare("
			SELECT
				cloud_sites.id, cloud_sites.master_domain,
				cloud_accounts.id AS account_id, cloud_accounts.is_cancelled, cloud_accounts.is_demo, UNIX_TIMESTAMP(cloud_accounts.date_demo_expire) AS date_demo_expire
			FROM cloud_sites
				LEFT JOIN cloud_accounts ON (cloud_accounts.cloud_site_id = cloud_sites.id)
			WHERE cloud_sites.master_domain = :domain
		");
		$q->execute(array(':domain' => $this->to_domain));

		return $q->fetch(PDO::FETCH_ASSOC);
	}


	/**
	 * @return \PDO
	 */
	protected function getCloudDb()
	{
		static $pdo;

		if (!$pdo) {
			$pdo = new \PDO(
				sprintf("mysql:host=%s;dbname=%s", DP_CLOUD_DATABASE_HOST, DP_CLOUD_DATABASE_NAME),
				DP_CLOUD_DATABASE_USER,
				DP_CLOUD_DATABASE_PASSWORD
			);
		}

		return $pdo;
	}


	/**
	 * @param array $siteinfo
	 */
	public function uploadToSite(array $siteinfo = null)
	{
		$url = str_replace('{PARAMS}', 'cat=' . urlencode($this->to_addr), DP_CLOUD_SAVEMAIL_URL);
		$url = str_replace('{DOMAIN}', $this->to_domain, $url);

		if (DP_CLOUD_SAVEMAIL_IP) {
			$urlinfo = parse_url($url);
			if (empty($urlinfo['scheme'])) $urlinfo['scheme']  = 'https';
			if (empty($urlinfo['path']))   $urlinfo['path']    = '/';
			if (empty($urlinfo['query']))  $urlinfo['query']   = '';
			if (empty($urlinfo['host']))   $urlinfo['host']    = '';

			$use_url = $urlinfo['scheme'] . '://' . DP_CLOUD_SAVEMAIL_IP . $urlinfo['path'] . '?' . $urlinfo['query'];

			$this->log(sprintf("uploadToSite: ip(%s)   use_url(%s)", DP_CLOUD_SAVEMAIL_IP, $use_url));
			$cmd = sprintf("curl -s -S -F mailfile=@%s -H %s %s", escapeshellarg($this->savepath), escapeshellarg('Host: ' . $urlinfo['host']), escapeshellarg($use_url));
		} else {
			$this->log(sprintf("uploadToSite: url(%s)", $url));
			$cmd = sprintf("curl -s -S -F mailfile=@%s %s", escapeshellarg($this->savepath), escapeshellarg($url));
		}

		$this->log("uploadToSite: curl: $cmd");

		$ret = null;
		$out = array();
		exec($cmd, $out, $ret);

		if (!$out) {
			$out = array();
		}

		$out = implode("\n", $out);

		$this->log(sprintf("uploadToSite: out: %s", $out));

		if (strpos($out, 'DP_UNKNOWN_CAT') !== false) {
			$this->log("uploadToSite: Site has no defiend address");
			$this->markUnknown();
			$this->exit_string = "Site has no such defined address";
			$this->exit_code = 3;
		} elseif (strpos($out, 'DP_MAIL_ACCEPT') === false) {
			$this->log("uploadToSite: Site rejected the message");
			$this->markFailed();
			$this->exit_string = "Message was rejected";
		}
	}

	####################################################################################################################

	protected function markFailed()
	{
		if (!$this->is_retry || $this->is_retry < DP_CLOUD_RETRY_LIMIT) {
			$dir = DP_CLOUD_MAILSTORE . '/_failed_retry';
			$this->log("markFailed: failed_retry");
		} else {
			$dir = DP_CLOUD_MAILSTORE . '/_failed';
			$this->log("markFailed: failed");
		}

		if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
			$this->log("markFailed: failed to make dir: $dir", self::ERR);
			return;
		}

		$path = $dir . '/' . date('Y-m-d') . '-' . basename($this->savepath, '.eml');
		$this->log("markFailed: path: $path", self::ERR);

		file_put_contents(
			$path,
			$this->getFailedLogString()
		);

		// Also append ongoing log
		@file_put_contents(
			DP_CLOUD_MAILSTORE . '/retry-log.log',
			sprintf("[%s] Sent To: %s    Attempts: %d    Save Path: %s", date('Y-m-d H:i:s'), $this->to_addr, $this->is_retry, $this->to_mailbox),
			\FILE_APPEND
		);
	}

	protected function markUnknown()
	{
		if (!is_dir(DP_CLOUD_MAILSTORE . '/_unknown') && !mkdir(DP_CLOUD_MAILSTORE . '/_unknown', 0755, true)) {
			return;
		}

		file_put_contents(
			DP_CLOUD_MAILSTORE . '/_unknown/' . date('Y-m-d') . '-' . basename($this->savepath, '.eml'),
			$this->getFailedLogString()
		);
	}

	protected function log($message, $level = self::DEBUG)
	{
		$this->log_messages[] = array(
			'time'    => time(),
			'message' => $message,
			'level'   => $level
		);
	}

	public function getFailedLogString()
	{
		$write_string = "<dp:data>\n";
		$write_string .= json_encode(array(
			'to_addr'     => $this->to_addr,
			'to_mailbox'  => $this->to_mailbox,
			'to_domain'   => $this->to_domain,
			'savepath'    => $this->savepath,
			'is_retry'    => $this->is_retry
		));
		$write_string .= "</dp:data>\n";
		$write_string .= "<dp:log>\n";
		$write_string .= $this->logAsString();
		$write_string .= "</dp:log>\n";

		return $write_string;
	}

	public function logAsString()
	{
		$write = array();
		foreach ($this->log_messages as $info) {
			$write[] = sprintf("<%s> [%s] %s", $this->session_id, date('Y-m-d H:i:s', $info['time']), $info['message']);
		}

		$write = implode("\n", $write);

		return $write;
	}

	public function flushLogIfLevel($level)
	{
		$do = false;
		foreach ($this->log_messages as $info) {
			if ($info['level'] >= $level) {
				$do = true;
				break;
			}
		}

		if ($do) {
			$this->flushLog();
		}
	}

	public function flushLog()
	{
		if (!DP_CLOUD_MAILLOG_PATH) {
			return;
		}

		$write = array();
		foreach ($this->log_messages as $info) {
			if (!empty($info['_written'])) {
				continue;
			}

			$write[] = sprintf("<%s> [%s] %s", $this->session_id, date('Y-m-d H:i:s', $info['time']), $info['message']);
		}

		if (!$write) {
			return;
		}

		$write = implode("\n", $write);
		$write .= "\n";

		$fp = fopen(DP_CLOUD_MAILLOG_PATH, 'a');
		if (!$fp) {
			return;
		}

		fwrite($fp, $write);
		fflush($fp);
		fclose($fp);
	}
}

DeskPRO_Cloud_ProcMail::exec();