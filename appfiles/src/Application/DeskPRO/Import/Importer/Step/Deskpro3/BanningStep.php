<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Import\Importer\Step\AbstractStep;

class BanningStep extends AbstractStep
{
	/**
	 * @var \Application\DeskPRO\Import\Importer\Deskpro3Importer
	 */
	protected $importer;

	public function getTitle()
	{
		return 'Import Banned Emails and IPs';
	}

	public function run()
	{
		$this->importer->getDb()->beginTransaction();

		try {
			$this->importIpBans();
			$this->importEmailBans();
			$this->importEmailGroupBans();

			$this->importer->getDb()->commit();
		} catch (\Exception $e) {
			$this->importer->getDb()->rollback();
			throw $e;
		}
	}


	/**
	 * Imports IP bans from the serialized 'ip_ban' data record.
	 */
	protected function importIpBans()
	{
		$this->importer->logMessage("Processing IP bans");
		$ip_bans = $this->importer->getOldDb()->fetchColumn("SELECT data FROM data WHERE name = 'ip_ban'");
		if (!$ip_bans) {
			$this->importer->logMessage("-- None (no data record)");
			return;
		}

		$ip_bans = @unserialize($ip_bans);
		if (!$ip_bans) {
			$this->importer->logMessage("-- None (empty or invalid)");
			return;
		}

		$this->importer->logMessage(sprintf("-- Importing %d bans", count($ip_bans)));

		$start_time = microtime(true);

		foreach ($ip_bans as $ip) {
			$banip = new \Application\DeskPRO\Entity\BanIp();
			$banip->setBannedIp($ip);
			$this->importer->getContainer()->getEm()->persist($banip);
		}
		$this->importer->getContainer()->getEm()->flush();

		$end_time = microtime(true);
		$this->importer->logMessage(sprintf("-- Done. Took %.3f seconds.", $end_time-$start_time));
	}


	/**
	 * Imports email bans from the 'ban_email' table for specific bans.
	 */
	public function importEmailBans()
	{
		$this->importer->logMessage("Processing banned email addresses");
		$email_bans = $this->importer->getOldDb()->fetchAllCol("SELECT email FROM ban_email");

		if (!$email_bans) {
			$this->importer->logMessage("-- None");
		}

		$this->importer->logMessage(sprintf("-- Importing %d addresses", count($email_bans)));

		$start_time = microtime(true);

		foreach ($email_bans as $email) {
			$this->importer->getDb()->insert('ban_emails', array('banned_email' => $email));
		}

		$end_time = microtime(true);
		$this->importer->logMessage(sprintf("-- Done. Took %.3f seconds.", $end_time-$start_time));
	}


	/**
	 * Imports email bans from the serialized 'email_group' data record for wildcard bans
	 */
	public function importEmailGroupBans()
	{
		$this->importer->logMessage("Processing banned email addresses with wildcards");
		$email_bans = $this->importer->getOldDb()->fetchColumn("SELECT data FROM data WHERE name = 'ip_ban'");
		if (!$email_bans) {
			$this->importer->logMessage("-- None (no data record)");
			return;
		}

		$email_bans = @unserialize($email_bans);
		if (!$email_bans) {
			$this->importer->logMessage("-- None (empty or invalid)");
			return;
		}

		$this->importer->logMessage(sprintf("-- Importing %d bans", count($email_bans)));

		$start_time = microtime(true);

		foreach ($email_bans as $email) {
			$this->importer->getDb()->insert('ban_emails', array('banned_email' => $email));
		}

		$end_time = microtime(true);
		$this->importer->logMessage(sprintf("-- Done. Took %.3f seconds.", $end_time-$start_time));
	}
}
