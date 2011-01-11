<?php

namespace Application\DeskPRO\Build;

use \Orb\Util\Strings;

/**
 * Versions are dates and times.
 *
 * A version string is a standard date and time in this format:
 * <var>Y-m-d H:i:s</var>
 * Example: <var>2010-11-26 12:29:00</var>
 *
 * In the system we represent versions as DateTime objects.
 *
 * When an identifier is required, such as part of a classname, all non-digits
 * are removed and the version looks like <var>20101126122900</var>.
 */
class VersionReader
{
	public static function getCurrentVersion($vfile = null)
	{
		if ($vfile === null) {
			$vfile = DP_ROOT.'/sys/VERSION';
		}

		$version = @file_get_contents($vfile);
		if ($version) {
			$version = Strings::getFirstLine($version);
			$version = trim($version);
			$version = \DateTime::createFromFormat("Y-m-d H:i:s", $version);
		}

		if (!$version) {
			throw new \DomainException('VERSION file does not exist or has an invalid value ('.$vfile.')');
		}

		return $version;
	}

	public static function getVersionFromId($version_id)
	{
		$version_id = (string)$version_id;
		if (strlen($version_id) != 14 OR !ctype_digit($version_id)) {
			throw new \DomainException("Invalid version id: `$version_id`");
		}

		$m = null;
		preg_match('#^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$#', $version_id, $m);

		$version = \DateTime::createFromFormat(
			"Y-m-d H:i:s",
			"{$m[1]}-{$m[2]}-{$m[3]} {$m[4]}:{$m[5]}:{$m[6]}"
		);

		return $version;
	}

	public static function getVersionFromString($version)
	{
		$version = \DateTime::createFromFormat("Y-m-d H:i:s", $version);

		return $version;
	}
	
	public static function getVersionId($version)
	{
		if (is_string($version)) {
			$version = preg_replace('#[^\d]#', '', $version);
		} elseif ($version instanceof \DateTime) {
			$version = $version->format('YmdHis');
		}

		return $version;
	}

	public static function getVersionString(\DateTime $version)
	{
		return $version->format('Y-m-d H:i:s');
	}
}