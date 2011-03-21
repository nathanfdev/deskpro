<?php

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use \Application\DeskPRO\Build\VersionReader;
use \Application\DeskPRO\Build\Upgrader;
use \Application\DeskPRO\App;

class UpgradeCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setName('dpdev:upgrade')
			->addOption('upgrade', null, InputOption::VALUE_NONE, 'Perform any neccessary upgrades');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Check VERSION file exists and is writable
		$vfile = DP_ROOT.'/sys/VERSION';
		if (!is_file($vfile) OR !is_readable($vfile) OR !is_writable($vfile)) {
			$output->writeln("<error>/sys/VERSION must exist, and be readable and writable</error>");
			return -1;
		}

		try {
			$version = VersionReader::getCurrentVersion();
		} catch (\DomainException $e) {
			$output->writeln("<error>/sys/VERSION contains an invalid version</error>");
			return -1;
		}

		if ($input->getOption('upgrade')) {
			return $this->executeUpgrade($input, $output);
		}

		$upgrader = new Upgrader();

		$version = VersionReader::getCurrentVersion();
		$source_version = $upgrader->getNewestVersion();

		$output->write("Current Version: " . VersionReader::getVersionString($version) . " (" . VersionReader::getVersionId($version) . ")\n");
		$output->write("Source Version:  " . VersionReader::getVersionString($source_version) . " (" . VersionReader::getVersionId($source_version) . ")\n");

		$behind = $upgrader->countNewerThan($version);
		if ($behind) {
			$output->writeln("Versions Behind: " . $behind);
			foreach ($upgrader->getAllNewer($version) as $newer_version) {
				$output->write("\t- " . VersionReader::getVersionString($newer_version) . " (" . VersionReader::getVersionId($newer_version) . ")\n");
			}

			$output->write("\n\n");
			$output->write("<info>Use the --upgrade switch to perform all the required upgrades</info>");
		} else {
			$output->write("All up to date.\n");
		}

		$output->write("\n");
	}

	protected function executeUpgrade(InputInterface $input, OutputInterface $output)
	{
		$upgrader = new Upgrader();

		$version = VersionReader::getCurrentVersion();
		$source_version = $upgrader->getNewestVersion();

		$output->write("Current Version: " . VersionReader::getVersionString($version) . " (" . VersionReader::getVersionId($version) . ")\n");
		$output->write("Source Version:  " . VersionReader::getVersionString($source_version) . " (" . VersionReader::getVersionId($source_version) . ")\n");

		$did = false;
		foreach ($upgrader->getAllNewer($version) as $newer_version) {
			$did = true;
			$output->writeln("<info>### Upgrading to " . VersionReader::getVersionString($newer_version) . " (" . VersionReader::getVersionId($newer_version) . ")");
			$upgrader->performUpgrade($newer_version, $output);
		}

		if (!$did) {
			// Always regen proxies even if no upgrade required,
			// the test-instant site uses this command to update it itself
			try {
				$warmer = new \Symfony\Bundle\DoctrineBundle\CacheWarmer\ProxyCacheWarmer(App::getContainer());
				$warmer->warmUp(null /* doctrine has its own config for cache dir */);
			} catch (Exception $e) {}
		}

		$output->write("\n");
	}
}
