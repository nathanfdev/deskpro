<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\SmtpConfig;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Languages\Build\OneSkyBuild;
use Application\DeskPRO\Languages\Build\TransifexBuild;
use Application\InstallBundle\Util\GenBuildManifest;
use Orb\Types\JsonObjectSerializer;
use Orb\Util\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class DevCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dpdev');
		$this->addOption('regen-build-manifest', null, InputOption::VALUE_NONE, 'Regenerate build-manifest.php file');
		$this->addOption('testdb-safe', null, InputOption::VALUE_NONE, 'Removes or rewrites some common settings to make the database safe to use');
		$this->addOption('testdb-rewrite-emails', null, InputOption::VALUE_REQUIRED, 'Rewrites all email addresses to be at the domain provided. someone@example.com becomes someone-at-example-com@domain.com');
		$this->addOption('preview', null, InputOption::VALUE_NONE, 'Preview');
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public function getContainer()
	{
		return parent::getContainer();
	}


	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int|null
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption('regen-build-manifest')) {
			return $this->regenBuildManifestAction($input, $output);
		} elseif ($input->getOption('testdb-safe')) {
			return $this->testdbSafeAction($input, $output);
		} elseif ($input->getOption('testdb-rewrite-emails')) {
			return $this->testdbRewriteEmailsAction($input, $output);
		} else {
			$output->write("<error>Unknown command</error>");
			return 1;
		}
	}


	private function testdbSafeAction(InputInterface $input, OutputInterface $output)
	{
		$db = $this->getContainer()->getDb();

		$output->writeln("Making sure core.redirect_correct_url is off");
		$this->getContainer()->getSettingsHandler()->setSetting('core.redirect_correct_url', 0);
		$output->writeln("-> OK");

		$output->writeln("Nulling email accounts -> Blank POP3 account with mailcatcher smtp");

		$incoming = new Pop3Config();
		$incoming->host = 'localhost';
		$incoming->port = '110';
		$incoming = JsonObjectSerializer::serialize($incoming);

		$out = new SmtpConfig();
		$out->host = 'localhost';
		$out->port = '1025';
		$out = JsonObjectSerializer::serialize($out);

		$db->executeUpdate("UPDATE email_accounts SET incoming_account = ?, outgoing_account = ?", array($incoming, $out));

		$output->writeln("-> OK");

		$output->writeln("Clearing out some tables");

		$tables = array(
			'visitor_tracks',
			'visitors',
			'twitter_accounts',
			'twitter_accounts_followers',
			'twitter_accounts_friends',
			'twitter_accounts_person',
			'twitter_accounts_searches',
			'twitter_accounts_searches_statuses',
			'twitter_accounts_statuses',
			'twitter_accounts_statuses_notes',
			'twitter_statuses',
			'twitter_statuses_long',
			'twitter_statuses_mentions',
			'twitter_statuses_tags',
			'twitter_statuses_urls',
			'twitter_stream',
			'twitter_users',
			'twitter_users_followers',
			'twitter_users_friends',
			'result_cache',
			'page_view_log',
			'client_messages',
			'agent_activity',
			'sessions',
		);

		$db->exec("SET FOREIGN_KEY_CHECKS = 0");
		foreach ($tables as $t) {
			try {
				echo "Delete from $t";
				$db->exec("DELETE FROM $t");
				echo "-> OK\n";
			} catch (\Exception $e) {
				echo "-> Fail: {$e->getMessage()}\n";
			}
		}
		foreach ($tables as $t) {
			try {
				echo "Truncate $t";
				$db->exec("TRUNCATE TABLE $t");
				echo "-> OK\n";
			} catch (\Exception $e) {
				echo "-> Fail: {$e->getMessage()}\n";
			}
		}
		$db->exec("SET FOREIGN_KEY_CHECKS = 1");

		$output->writeln("Removing pictures, css, other common blobs that will fail to laod");
		$db->executeUpdate("UPDATE people SET picture_blob_id = null");
		$db->executeUpdate("UPDATE departments SET avatar_blob_id = null");
		$db->executeUpdate("UPDATE agent_teams SET avatar_blob_id = null");
		$db->executeUpdate("UPDATE styles SET logo_blob_id = null, css_blob_id = null, css_blob_rtl_id = null");
		$this->getContainer()->getSettingsHandler()->setSetting('core.favicon_blob_url', null);
		$output->writeln("-> OK");
	}


	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	private function testdbRewriteEmailsAction(InputInterface $input, OutputInterface $output)
	{
		$output->writeln("Running...");
		$db = $this->getContainer()->getDb();

		$output->writeln("Setting 'comment' to the original email");
		$db->executeUpdate("UPDATE people_emails SET comment = email");
		$output->writeln("-> OK");

		$output->writeln("Replacing at character");
		$db->executeUpdate("UPDATE people_emails SET email = REPLACE(email, '@', '-at-')");
		$output->writeln("-> OK");

		$output->writeln("Replacing dots");
		$db->executeUpdate("UPDATE people_emails SET email = REPLACE(email, '.', '-')");
		$output->writeln("-> OK");

		$domain = $input->getOption('testdb-rewrite-emails');
		$output->writeln("Setting new domain: $domain");
		$db->executeUpdate("UPDATE people_emails SET email = CONCAT(email, '@$domain')");
		$output->writeln("-> OK");

		$output->writeln("All done");
		return 0;
	}


	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	private function regenBuildManifestAction(InputInterface $input, OutputInterface $output)
	{
		$manifest_path = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/build-manifest.php';
		$builds_path   = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build';

		$gen = new GenBuildManifest($builds_path);
		$file = $gen->getContents();

		if ($input->getOption('preview')) {
			echo $file;
			return 0;
		} else {
			if (file_put_contents($manifest_path, $file)) {
				echo "Wrote manifest file: $manifest_path\n";
				return 0;
			} else {
				echo "Failed to write manifest file: $manifest_path\n";
				return 1;
			}
		}
	}
}