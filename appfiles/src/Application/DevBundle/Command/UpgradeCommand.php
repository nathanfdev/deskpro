<?php

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use \DeskPRO\Build\VersionReader;
use \DeskPRO\Build\Upgrader;

class UpgradeCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:upgrade');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Check VERSION file exists and is writable
		$vfile = ROOT.'/sys/VERSION';
		if (!is_file($vfile) OR !is_readable($vfile) OR !is_writable($vfile)) {
			$output->write("<warn>/sys/VERSION must exist, and be readable and writable</warn>");
			return -1;
		}

		try {
			$version = VersionReader::getCurrentVersion();
		} catch (\DomainException $e) {
			$output->write("<warn>/sys/VERSION contains an invalid version</warn>");
			return -1;
		}

		$upgrader = new Upgrader();

		$source_version = $upgrader->getNewestVersion();

		$output->write("Current Version: " . VersionReader::getVersionString($version) . " (" . VersionReader::getVersionId($version) . ")\n");
		$output->write("Source Version:  " . VersionReader::getVersionString($source_version) . " (" . VersionReader::getVersionId($source_version) . ")\n");

		$behind = $upgrader->countNewerThan($version);
		if ($behind) {
			$output->write("Versions Behind: " . $behind);
			foreach ($upgrader->getAllNewer($version) as $newer_version) {
				$output->write(VersionReader::getVersionString($newer_version) . " (" . VersionReader::getVersionId($newer_version) . ")\n");
			}

			$output->write("\n\n");
			$output->write("<info>Use the --upgrade switch to perform all the required upgrades</info>");
		}

		$output->write("\n");
	}
}
