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
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\DevBundle\Controller;

use Application\DeskPRO\Build\VersionReader;
use Application\DeskPRO\Build\Upgrader;

class BuildController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	public function init()
	{
		// Check VERSION file exists and is writable
		$vfile = DP_ROOT.'/sys/VERSION';
		if (!is_file($vfile) OR !is_readable($vfile) OR !is_writable($vfile)) {
			return $this->createResponse('ERROR: /sys/VERSION must exist, and be readable and writable', 500);
		}

		try {
			$version = VersionReader::getCurrentVersion();
		} catch (\DomainException $e) {
			return $this->createResponse('ERROR: /sys/VERSION contains an invalid version', 500);
		}
	}

	public function indexAction()
	{
		$upgrader = new Upgrader();

		$version = VersionReader::getCurrentVersion();
		$version_id = VersionReader::getVersionId($version);
		$source_version = $upgrader->getNewestVersion();
		$source_version_id = VersionReader::getVersionId($source_version);

		$behind = $upgrader->countNewerThan($version);
		$behind_versions = array();
		if ($behind) {
			foreach ($upgrader->getAllNewer($version) as $newer_version) {
				$behind_versions[] = array(
					'version' => VersionReader::getVersionString($newer_version),
					'version_id' => VersionReader::getVersionId($newer_version)
				);
			}
		}

		return $this->render('DevBundle:Build:index.html.php', array(
			'version_file'        => DP_ROOT.'/sys/VERSION',
			'version'             => VersionReader::getVersionString($version),
			'version_id'          => $version_id,
			'source_version'      => VersionReader::getVersionString($source_version),
			'source_version_id'   => $source_version_id,
			'behind_versions'     => $behind_versions,
			'all_versions'        => $upgrader->getUpgradeVersions()
		));
	}

	public function genBuildClassAction()
	{
		$build = new \DateTime();
		$build_id = VersionReader::getVersionId($build);
		$build_string = VersionReader::getVersionString($build);

		return $this->render('DevBundle:Build:gen-build-class.html.php', array(
			'build_id' => $build_id,
			'build_string' => $build_string,
			'build_classname' => 'Upgrade' . $build_id
		));
	}

	public function upgradeAction()
	{
		$upgrader = new Upgrader();

		$version = VersionReader::getCurrentVersion();

		$behind = $upgrader->countNewerThan($version);
		$behind_versions = array();
		if ($behind) {
			foreach ($upgrader->getAllNewer($version) as $newer_version) {
				$behind_versions[] = array(
					'version' => VersionReader::getVersionString($newer_version),
					'version_id' => VersionReader::getVersionId($newer_version)
				);
			}
		}

		if (!$behind) {
			// No upgrades to do
			return $this->redirectRoute('dev_build');
		}

		return $this->render('DevBundle:Build:upgrade.html.php', array(
			'version'             => VersionReader::getVersionString($version),
			'behind_versions'     => $behind_versions,
		));
	}

	public function upgradeDoAction()
	{
		echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Upgrade</title>
</head>
<body>
HTML;

		$upgrader = new Upgrader();
		$version = VersionReader::getVersionFromId($_GET['version_id']);

		echo '<strong>Performing ' . VersionReader::getVersionString($version) . '</strong><br /><br />';
		echo '<pre>';

		$output = new \Application\DeskPRO\Build\Output();
		$output->html = true;
		$status = $upgrader->performUpgrade($version, $output);

		echo '</pre>';

		echo '<script type="text/javascript">';
		echo 'if (parent && parent.Upgrader && parent.Upgrader.upgradeDone) {';
		echo 'parent.Upgrader.upgradeDone("' . VersionReader::getVersionId($version) . '", ';
			if ($status) {
				echo 'true';
			} else {
				echo 'false';
			}
		echo ');';
		echo '}';
		echo '</script>';
		echo '</body></html>';

		return $this->createResponse('');
	}
}
