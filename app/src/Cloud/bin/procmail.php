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
define('DP_CLOUD_SAVEMAIL_URL', 'http://example.com/deskpro/index.php?_sys=savemail&auth=XXX&{PARAMS}');

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

	public static function exec()
	{
		new self();
	}

	private function __construct()
	{
		date_default_timezone_set('UTC');

		if (!is_writable(DP_CLOUD_MAILSTORE)) {
			trigger_error("MAILSTORE is not writable: " . DP_CLOUD_MAILSTORE, E_USER_ERROR);
			exit(1);
		}

		$this->run();
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

		$this->saveToFilesystem();
		$this->saveToTarget();
	}

	####################################################################################################################

	/**
	 * Saves mail from STDIN to the mail directory
	 */
	protected function saveToFilesystem()
	{
		$this->savedir = DP_CLOUD_MAILSTORE . '/' . date('Y-m-d');

		if (!is_dir($this->savedir)) {
			if (!mkdir($this->savedir, 0755, true)) {
				trigger_error("Could not create MAILSTORE_DIR: $this->savedir", E_USER_ERROR);
				exit(1);
			}
		}

		$this->savepath = $this->savedir . '/' . date('H-i-s') . '-' . $this->to_domain . '.' . mt_rand(100000000,999999999) . '.eml';

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
			$this->markUnknown();
			return;
		}

		try {
			$cloudsite = $this->findCloudSite();
			if (!$cloudsite) {
				$this->markUnknown();
				return;
			}

			$this->uploadToSite($cloudsite);
		} catch (\Exception $e) {
			$this->markFailed();
			throw $e; // throw up to be logged
		}
	}

	/**
	 * @return array
	 */
	protected function findCloudSite()
	{
		$pdo = $this->getCloudDb();

		$q = $pdo->prepare("SELECT * FROM cloud_sites WHERE master_domain = :domain");
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
	public function uploadToSite(array $siteinfo)
	{
		$url = str_replace('{PARAMS}', 'cat=' . urlencode($this->to_domain), DP_CLOUD_SAVEMAIL_URL);
		$cmd = sprintf("curl -X PUT --data-binary @%s %s", $this->savepath, $url);

		$ret = $out = null;
		exec($cmd, $out, $ret);

		if (strpos($out, 'DP_MAIL_ACCEPT') === false) {
			$this->markFailed();
		}
	}

	####################################################################################################################

	protected function markFailed()
	{
		if (is_dir(DP_CLOUD_MAILSTORE . '/_failed') && !mkdir(DP_CLOUD_MAILSTORE . '/_failed', 0755, true)) {
			return;
		}

		file_put_contents(DP_CLOUD_MAILSTORE . '/_failed/' . date('Y-m-d') . '-' . basename($this->savepath, '.eml'), $this->savepath);
	}

	protected function markUnknown()
	{
		if (is_dir(DP_CLOUD_MAILSTORE . '/_unknown') && !mkdir(DP_CLOUD_MAILSTORE . '/_failed', 0755, true)) {
			return;
		}

		file_put_contents(DP_CLOUD_MAILSTORE . '/_unknown/' . date('Y-m-d') . '-' . basename($this->savepath, '.eml'), $this->savepath);
	}
}

DeskPRO_Cloud_ProcMail::exec();
