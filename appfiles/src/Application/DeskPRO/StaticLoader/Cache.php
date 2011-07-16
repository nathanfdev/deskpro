<?php
/**
 * DeskPRO
 *
 * @package StaticLoader
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\StaticLoader;


/**
 * This static loader is used with the DI to get a Zend Cache object.
 *
 * In sys/config/config.yml, something like:
 * <code>
 * deskpro_cache:
 *   someid: ~
 * </code>
 *
 * Where 'cache_id' is the key of the config. 'someid' means we'll load cache_someid.
 *
 * @see \Application\DeskPRO\DependencyInjection\CacheExtension
 */
class Cache
{
	static function getCache($name, $options, $cache_dir)
	{
		$key = 'cache.' . $name;
		$user_options = \Application\DeskPRO\App::getConfig($key);

		if ($user_options) {
			$options = array_merge($options, $user_options);
		}

		if (!$user_options) {
			return null;
		}

		if (!isset($options['backend'])) {
			throw new \InvalidArgumentException("Mising backend option for cache config `$key`");
		}

		$backend_name = $options['backend'];
		unset($options['backend']);

		$enable = true;
		if (\Application\DeskPRO\App::getConfig('disable_caching')) {
			$enable = false;
		}

		$frontend_options = array(
			'caching' => $enable,
			'cache_id_prefix' => $name,
			'lifetine' => null,
			'logging' => false,
			'write_control' => true,
			'automatic_serialization' => true,
			'automatic_cleaning_factor' => 0,
			'ignore_user_abort' => true
		);
		if (isset($options['frontend'])) {
			$frontend_options = array_merge($frontend_options, $options['frontend']);
			unset($options['frontend']);
		}

		// Used in File backend
		if (isset($options['cache_dir'])) {
			$options['cache_dir'] = self::_initCachePath($options['cache_dir'], $cache_dir, false);
		}

		// Sqlite
		if (isset($options['cache_db_complete_path'])) {
			$options['cache_db_complete_path'] = self::_initCachePath($options['cache_db_complete_path'], $cache_dir, true);
		}

		$cache = \Zend\Cache\Cache::factory('Core', $backend_name, $frontend_options, $options);

		return $cache;
	}

	protected static function _initCachePath($dir, $cache_dir, $is_file)
	{
		$dir = str_replace('%kernel.cache_dir%', $cache_dir, $dir);

		$realdir = $dir;
		if ($is_file) {
			$realdir = dirname($dir);
		}

		if (!is_dir($realdir)) {
			@mkdir($realdir, 0777, true);
		}

		return $dir;
	}
}