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
 * deskpro_cache.caches:
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
		$key = 'cache_' . $name;
		$user_options = \Application\DeskPRO\App::getConfig($key);

		if ($user_options) {
			$options = array_merge($options, $user_options);
		}

		if (!isset($options['backend'])) {
			throw new \InvalidArgumentException("Mising backend option for cache config `$key`");
		}

		$backend_name = $options['backend'];
		unset($options['backend']);

		$frontend_options = array(
			'caching' => true,
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
			$options['cache_dir'] = str_replace('%kernel.cache_dir%', $cache_dir, $options['cache_dir']);

			// Make sure it exists
			if (!is_dir($options['cache_dir'])) {
				if (@mkdir($options['cache_dir'])) {
					@chmod($options['cache_dir'], \Orb\Util\Util::ifsetor($options['hashed_directory_umask'], 0744));
				}
			}
		}

		// Used in SQLite backend
		if (isset($options['cache_db_complete_path '])) {
			$options['cache_db_complete_path'] = str_replace('%kernel.cache_dir%', $cache_dir, $options['cache_db_complete_path']);
		}

		$cache = \Zend\Cache\Cache::factory('Core', $backend_name, $frontend_options, $options);

		return $cache;
	}
}