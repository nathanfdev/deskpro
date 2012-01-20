<?php
/**
 * Orb
 *
 * @package Orb
 * @category File
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Distribution;

use Symfony\Component\Finder\Finder;

class ChecksumChecker extends \Orb\File\ChecksumChecker
{
	public function __construct($chunk_size = 200)
	{
		parent::__construct(realpath(DP_ROOT.'/../'));
		$this->finder->notName('distro-checksums.php')
			->notName('.gitignore')
			->notName('.DS_Store')
			->notName('dev_debug.php')
			->notName('config.php')
			->notName('config.new.php')
			->notName('classes.map')
			->ignoreVCS(true)
			->exclude('sys/cache/dev')
			->exclude('.idea');
	}

	/**
	 * Compare the current fileset with the distributed list
	 *
	 * @return array
	 */
	public function compareWithStandard()
	{
		return $this->compareWithDump(DP_ROOT.'/sys/Resources/distro-checksums.php');
	}


	/**
	 * Dump current hashes to standard checksum file for deskpro
	 */
	public function dumpToStardnardFile()
	{
		return $this->dumpToFile(DP_ROOT.'/sys/Resources/distro-checksums.php');
	}
}
