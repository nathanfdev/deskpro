<?php

namespace Application\DeskPRO\Build;

use Symfony\Component\Finder\Finder;

use Orb\Util\Arrays;

class Upgrader
{
	const STEP_DONE   = 1;
	const STEP_FAILED = 2;
	const STEP_AGAIN  = 3;

	protected $upgrade_versions = null;

	public function performUpgrade(\DateTime $version, $output)
	{
		$info = $this->getUpgradeInfo($version);
		$class = $info['classname'];

		$up = new $class($output);
		
		$cur_step = 1;
		$cur_step_sub = 0;

		$output->write("<info>Upgrade Class: " . \Orb\Util\Util::getBaseClassname($up) . "</info>");
		$output->write("<info>Steps: {$info['steps']}</info>");

		// We save the status to the VERSION file
		// Line1 (current version): 2010-11-26 12:29:00
		// Line2 (in progress up) : 2010-11-27 03:44:00(3,0)
		// Meaning step 3 is the one we're "on"
		$up_status = file($this->getVersionFilePath());
		if (isset($up_status[1])) {
			$up_status = $up_status[1];
			if (preg_match('#^(.{19})\((\d+),(\d+)\)$#', $up_status, $up_status)) {
				if ($up_status[1] == VersionReader::getVersionString($version)) {
					$cur_step = $up_status[2];
					$cur_step_sub = $up_status[3];

					$output->write("<info>Resuming from step $cur_step.$cur_step_sub</info>");
				}
			}
		}

		while ($cur_step <= $info['steps']) {
			$method = 'step' . $cur_step;

			$dostep = true;
			while ($dostep) {
				$dostep = false;

				$output->write("<info>Running $cur_step.$cur_step_sub</info>");
				$ret = $up->$method($cur_step_sub);

				if ($ret == self::STEP_AGAIN) {
					$cur_step_sub++;
					$dostep = true;

					$this->setUpgradeStatus($version, $cur_step, $cur_step_sub);
				} elseif ($ret == self::STEP_FAILED) {
					$output->write("<warn>STEP FAILED</warn>");
					return false;
				}
			}

			$cur_step_sub = 0; //reset
			$cur_step++; // go on to next step
			$this->setUpgradeStatus($version, $cur_step, $cur_step_sub);
		}

		$output->write("<info>UPGRADE COMPLETE</info>");
		$this->setCurrentVersion($version);

		$output->write("\n\n");

		return true;
	}


	public function getUpgradeInfo(\DateTime $version)
	{
		$refl = new \ReflectionClass($this->getUpgradeClass($version));

		$info = array();
		$info['classname'] = $refl->getName();
		$info['classfile'] = $refl->getFileName();
		$info['steps'] = 0;

		foreach ($refl->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			if (preg_match('#^step(\d+)$#', $method->name)) {
				$info['steps']++;
			}
		}

		return $info;
	}



	/**
	 * Get the classname of an upgrade class.
	 *
	 * @param \DateTime $version
	 * @return string
	 */
	public function getUpgradeClass(\DateTime $version)
	{
		return 'Application\\DeskPRO\\Build\\Upgrade\\Upgrade' . VersionReader::getVersionId($version);
	}



	/**
	 * Get an array of versions we know about.
	 * 
	 * @return array
	 */
	public function getUpgradeVersions()
	{
		if ($this->upgrade_versions === null) {
			$finder = new Finder();
			$finder->files()
				->in(DP_ROOT.'/src/Application/DeskPRO/Build/Upgrade')
				->name('/\\d{14}\.php/');

			foreach ($finder as $file) {
				$version_id = $file->getFilename();
				$version_id = preg_replace('#\.php$#', '', $version_id);
				$version_id = preg_replace('#^Upgrade#', '', $version_id);

				$this->upgrade_versions[] = VersionReader::getVersionFromId($version_id);
			}

			usort($this->upgrade_versions, function(\DateTime $a, \DateTime $b) {
				return ($a < $b) ? -1 : 1;
			});
		}

		return $this->upgrade_versions;
	}



	/**
	 * Count how many versions that are newer than our current.
	 *
	 * @param DateTime $version
	 * @return int
	 */
	public function countNewerThan(\DateTime $version)
	{
		$count = 0;
		foreach ($this->getUpgradeVersions() as $check_version) {
			if ($check_version > $version) {
				$count++;
			}
		}

		return $count;
	}


	
	/**
	 * Get an array of all newer versions
	 *
	 * @param DateTime $version
	 * @return array
	 */
	public function getAllNewer(\DateTime $version)
	{
		$ret = array();

		foreach ($this->getUpgradeVersions() as $check_version) {
			if ($check_version > $version) {
				$ret[] = $check_version;
			}
		}

		return $ret;
	}



	/**
	 * Get the next version we should upgrade to.
	 *
	 * @param DateTime $version
	 * @return DateTime
	 */
	public function getNextVersion(\DateTime $version)
	{
		foreach ($this->getUpgradeVersions() as $check_version) {
			if ($check_version > $version) {
				return $check_version;
			}
		}

		return null;
	}

	

	/**
	 * Get the last (newest) version we know about.
	 * 
	 * @return DateTime
	 */
	public function getNewestVersion()
	{
		return Arrays::getLastItem($this->getUpgradeVersions());
	}
	


	/**
	 * Sets the VERSION file to say we're on a certain version.
	 *
	 * @param DateTime $version
	 */
	public function setCurrentVersion(\DateTime $version)
	{
		if (!@file_put_contents($this->getVersionFilePath(), VersionReader::getVersionString($version))) {
			throw new \RuntimeException('Could not write version file: ' . $this->getVersionFilePath());
		}
	}



	/**
	 * Sets the status of an ongoing upgrade to store the last successful step and sub-step.
	 *
	 * @param DateTime $upgrade_version
	 * @param <type> $step
	 * @param <type> $sub_step
	 */
	public function setUpgradeStatus(\DateTime $upgrade_version, $step = '0', $sub_step = '0')
	{
		$contents = file($this->getVersionFilePath());
		$contents = array(
			trim($contents[0]),
			VersionReader::getVersionString($upgrade_version) . "($step,$sub_step)"
		);
		$contents = implode("\n", $contents);

		file_put_contents($this->getVersionFilePath(), $contents);
	}



	/**
	 * Get the path to the VERSION file.
	 * 
	 * @return string
	 */
	public function getVersionFilePath()
	{
		return DP_ROOT.'/sys/VERSION';
	}
}