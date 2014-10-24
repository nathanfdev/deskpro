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
use Application\InstallBundle\Util\GenBuildManifest;
use Orb\Types\JsonObjectSerializer;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Swagger\Swagger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DevCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dpdev');
		$this->addOption('regen-build-manifest', null, InputOption::VALUE_NONE, 'Regenerate build-manifest.php file');
		$this->addOption('touch-build-time', null, InputOption::VALUE_NONE, 'Sets build-time.php file to now');
		$this->addOption('testdb-safe', null, InputOption::VALUE_NONE, 'Removes or rewrites some common settings to make the database safe to use');
		$this->addOption('testdb-rewrite-emails', null, InputOption::VALUE_REQUIRED, 'Rewrites all email addresses to be at the domain provided. someone@example.com becomes someone-at-example-com@domain.com');
		$this->addOption('move-build-scripts', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of build scripts to re-timestamp from now. This is useful when merging an old branch and you want to move buildscripts "up".');
		$this->addOption('build-api-docs', null, InputOption::VALUE_NONE, 'Builds Swagger resource files');
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
		} elseif ($input->getOption('touch-build-time')) {
			return $this->touchBuildTimeAction($input, $output);
		} elseif ($input->getOption('testdb-safe')) {
			return $this->testdbSafeAction($input, $output);
		} elseif ($input->getOption('testdb-rewrite-emails')) {
			return $this->testdbRewriteEmailsAction($input, $output);
		} elseif ($input->getOption('build-api-docs')) {
			return $this->buildApiDocsAction($input, $output);
		} elseif ($input->getOption('move-build-scripts')) {
			return $this->moveBuildScriptsAction($input, $output);
		} else {
			$output->write("<error>Unknown command</error>");
			return 1;
		}
	}


	private function testdbSafeAction(InputInterface $input, OutputInterface $output)
	{
		$db = $this->getContainer()->getDb();

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


	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	private function touchBuildTimeAction(InputInterface $input, OutputInterface $output)
	{
		$time = time();
		$build_file = DP_ROOT.'/sys/config/build-time.php';
		file_put_contents($build_file, '<?php define("DP_BUILD_TIME", '.$time.'); ');

		echo "Updated: $build_file\n";
		return 0;
	}


	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	private function buildApiDocsAction(InputInterface $input, OutputInterface $output)
	{
		$start_time = microtime(true);

		$save_path = DP_ROOT.'/src/Application/ApiBundle/Resources/views/SwaggerDocs';

		$output->writeln("Generating Swagger resources");
		$output->writeln("-> Path: $save_path");

		$output->writeln("Removing old files");
		$fs = new Filesystem();
		$fs->remove($save_path);
		$fs->mkdir($save_path, 0755);
		$output->writeln("-> OK");

		$output->writeln("Scanning ...");
		$swagger = new Swagger(DP_ROOT.'/src/Application/ApiBundle');
		$output->writeln("-> OK");

		$output->writeln("Generating resource-list.json...");
		file_put_contents($save_path.'/deskpro-api.json', $swagger->getResourceList(array('output' => 'json')));
		$fs->chmod($save_path.'/deskpro-api.json', 0644);

		$output->writeln("-> OK");

		foreach ($swagger->getResourceNames() as $res) {
			$output->writeln("Generating $res.json...");
			file_put_contents($save_path."/$res.json", $swagger->getResource($res, array('output' => 'json')));
			$fs->chmod($save_path."/$res.json", 0644);
			$output->writeln("-> OK");
		}

		$output->writeln(sprintf("All done in %.4fs", microtime(true)-$start_time));

		return 0;
	}


	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	private function moveBuildScriptsAction(InputInterface $input, OutputInterface $output)
	{
		$builds_root   = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build';
		$build_ids_raw = explode(',', trim($input->getOption('move-build-scripts', ''), ','));
		$build_ids     = array();

		$get_file_path = function($v) use ($builds_root) {
			$y = @date('Y', $v);
			$m = @date('m', $v);
			return $builds_root . "/$y/$m/Build$v.php";
		};

		foreach ($build_ids_raw as $bid) {
			$b = preg_replace('/[^0-9]/', '', $bid);
			$file = $get_file_path($b);
			if (!$b || !$file) {
				$output->writeln("<error>Invalid build script: $bid</error>");
				return 1;
			}
			if (!is_file($file)) {
				$output->writeln("<error>Invalid build script: $bid -- No file: $file</error>");
				return 1;
			}

			$build_ids[] = $b;
		}

		if (!$build_ids) {
			$output->writeln("<error>No builds specified</error>");
			return 1;
		}

		sort($build_ids, SORT_NUMERIC);

		$start = time();
		foreach ($build_ids as $bid) {
			$start++;
			$new_bid = $start;

			$file = $get_file_path($bid);
			$new_file = $get_file_path($new_bid);

			$output->writeln("<info>$bid -> $new_bid</info>");

			rename($file, $new_file);
			$output->writeln("\tOld Path: $file");
			$output->writeln("\tNew Path: $new_file");
			$output->writeln("");

			$f = file_get_contents($new_file);
			$f = str_replace('Build'.$bid, 'Build'.$new_bid, $f);
			file_put_contents($new_file, $f);
		}

		$output->writeln("Done");
		$output->writeln("You will now want to regen the build-time and build-manifest:");
		$output->writeln("\tphp cmd.php dpdev --touch-build-time");
		$output->writeln("\tphp cmd.php dpdev --regen-build-manifest");
		return 0;
	}
}