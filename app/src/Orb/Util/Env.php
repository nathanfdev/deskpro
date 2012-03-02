<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Util;

use Orb\Util\Numbers;

/**
 * Helps fetch stuff about the server/environment
 *
 * @static
 */
class Env
{
	/**
	 * Static class
	 */
	private function __construct() {}

	/**
	 * Return 'upload_max_filesize' size in bytes
	 *
	 * @return int
	 */
	public static function getMaxUploadSize()
	{
		$size = @ini_get('upload_max_filesize');
		if (!$size) {
			return 0;
		}

		return Numbers::parseIniSize($size);
	}


	/**
	 * Return 'post_max_size' size in bytes
	 *
	 * @return int
	 */
	public static function getMaxPostSize()
	{
		$size = @ini_get('post_max_size');
		if (!$size) {
			return 0;
		}

		return Numbers::parseIniSize($size);
	}


	/**
	 * Get the size in bytes of the effective maximum upload size.
	 *
	 * Upload size is determined by the smallest of these three settings:
	 * - upload_max_filesize
	 * - post_max_size
	 * - memory_limit (just because you need memory to accept the file)
	 *
	 * @return int
	 */
	public static function getEffectiveMaxUploadSize()
	{
		$min = min(self::getMaxUploadSize(), self::getMaxPostSize());

		$mem = self::getMemoryLimit();
		if ($mem != -1) {
			$min = min($min, $mem);
		}

		return $min;
	}


	/**
	 * Return 'memory_limit' size in bytes or -1 if there is no limit
	 *
	 * @return int
	 */
	public static function getMemoryLimit()
	{
		$size = @ini_get('memory_limit');
		if ($size == -1) {
			return -1;
		}

		return Numbers::parseIniSize($size);
	}


	/**
	 * Gets the path to the laoded php.ini file by scanning phpinfo
	 *
	 * @return false|string
	 */
	public static function getPhpIniPath()
	{
		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();
		$phpinfo = html_entity_decode(strip_tags($phpinfo), ENT_QUOTES);

		if (preg_match('#^Loaded Configuration File (.*?)$#m', $phpinfo, $m)) {
			return $m[1];
		} else {
			return false;
		}
	}
}
