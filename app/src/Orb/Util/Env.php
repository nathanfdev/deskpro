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
 * Orb
 *
 * @package Orb
 * @category Util
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
			$min = min($min, $mem / 3);
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
		if (self::isFunctionDisabled('phpinfo')) {
			return false;
		}

		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();
		$phpinfo = html_entity_decode(strip_tags($phpinfo), ENT_QUOTES);

		if (preg_match('#^Loaded Configuration File (.*?)$#m', $phpinfo, $m)) {
			$path = $m[1];
			$path = str_replace('=>', '', $path);
			$path = trim($path);
			return $path;
		} else {
			return false;
		}
	}


	/**
	 * Check if a function has been disabled in php.ini with 'disable_functions'
	 *
	 * @param string $func_name
	 * @return string
	 */
	public static function isFunctionDisabled($func_name)
	{
		static $disabled = null;

		if ($disabled === null) {
			$disabled = explode(',', ini_get('disable_functions'));
			foreach ($disabled as &$_v) {
				$_v = trim(strtolower($_v));
			}

			$disabled = array_flip($disabled);
		}

		$func_name = strtolower($func_name);

		return isset($disabled[$func_name]);
	}
}
