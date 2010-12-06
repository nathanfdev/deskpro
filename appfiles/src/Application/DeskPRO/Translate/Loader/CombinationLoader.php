<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Translate\Loader;

use \Orb\Util\Arrays;

/**
 * Combines multiple loaders, and also adds a cache layer.
 * TODO may want to abstract caching out into a decorator
 */
class CombinationLoader implements LoaderInterface
{
	/**
	 * @var array
	 */
	protected $loaders = array();

	/**
	 * @var \Zend\Cache\Frontend\Core
	 */
	protected $cache;

	/**
	 * The key to prefix caches with. This will be the language ID.
	 * @var string
	 */
	protected $cache_prefix;

	
	
	/**
	 * Set a cacher to cache phrasegroups.
	 *
	 * The cache prefix is used so multiple languages can be use the same cache object.
	 * So, for example, the prefix might be an ID.
	 *
	 * Setting cache handler to null effectively disables a previously set cache.
	 *
	 * @param \Zend\Cache\Frontend $cache
	 * @param string $cache_prefix
	 */
	public function setCache(\Zend\Cache\Frontend $cache, $cache_prefix = '')
	{
		if ($cache === null) {
			$this->cache = null;
			$this->cache_prefix = null;
			return;
		}

		$this->cache = $cache;
		$this->cache_prefix = $cache_prefix;
	}



	/**
	 * Gets the currently set cache, or null if there is none set.
	 *
	 * @return \Zend\Cache\Frontend
	 */
	public function getCache()
	{
		return $this->cache;
	}



	/**
	 * Gets the cache prefix used
	 *
	 * @return string
	 */
	public function getCachePrefix()
	{
		return $this->cache_prefix;
	}

	

	/**
	 * Add a loader to the chain. Later loaders override phrases fetched from
	 * previous loaders.
	 *
	 * @param LoaderInterface $loader
	 */
	public function addLoader(LoaderInterface $loader)
	{
		$this->loaders[] = $loader;
	}


	
	/**
	 * Loads phrase groups
	 * 
	 * @param array $groups Groups to load
	 * @return array
	 */
	public function load($groups)
	{
		#------------------------------
		# See if we can fetch any fully-formed groups from the cache
		#------------------------------

		$cached_phrases = array();
		if ($this->cache) {

			// Phrases are cached per-group, so we need to fetch them one at a time
			// them merge them into one big array that we'll merge again into $phrases at the end

			foreach (array_keys($groups) as $k) {
				$loaded_cache_phrases = $this->_loadGroupFromCache($groups[$k]);
				if (is_array($loaded_cache_phrases)) {
					$cached_phrases = array_merge($cached_phrases, $loaded_cache_phrases);

					// Since we got it from cache, we dont need to fetch it from
					// the loaders below
					unset($groups[$k]);
				}
			}

			unset($loaded_cache_phrases);
		}

		#------------------------------
		# Otherwise we'll need to fetch them from the loaders
		#------------------------------

		$phrases = array();
		if ($groups) {
			$grouped_phrases = array();

			foreach ($this->loaders as $loader) {
				try {
					$loader_phrases = $loader->load($groups);

					// Loaders return array(group=>array(phrases), group2=>array(phrases)..)
					// So we'll merge all loader groups into one master array
					// Because we may also want to cache the groups after

					foreach ($loader_phrases as $group => $groupphrases) {
						if (!isset($grouped_phrases[$group])) {
							$grouped_phrases[$group] = $groupphrases;
						} else {
							$grouped_phrases[$group] = array_merge($grouped_phrases[$group], $groupphrases);
						}
					}
				} catch (Exception $e) {}
			}

			// If we have a cache, we'll go and cache the groups we just got
			if ($this->cache) {
				$this->_saveGroupedPhrasesToCache($grouped_phrases);
			}

			// $group_phrases is divided into groups, the phrases array we want
			// to return is just one big array, so merge it down now
			$phrases = Arrays::mergeSubArrays($grouped_phrases);
		}

		// If we got cached phrases we'll fold those into the phrases array now too
		if ($cached_phrases) {
			$phrases = array_merge($cached_phrases, $phrases);
		}

		return $phrases;
	}



	/**
	 * Try to load a group of phrases from the cache. Returns an array
	 * if there was some, false on error.
	 *
	 * @param string $group A groupname
	 * @return array
	 */
	protected function _loadGroupFromCache($group)
	{
		$id = $this->cache_prefix . $group;

		$phrases = $this->cache->load($id);

		if (!is_array($phrases)) {
			return false;
		}

		return $phrases;
	}
	


	/**
	 * Save a bunch of phrase groups to the cache.
	 *
	 * @param array $grouped_phrases
	 */
	protected function _saveGroupedPhrasesToCache(array $grouped_phrases)
	{
		foreach ($grouped_phrases as $group => $phrases) {
			$id = $this->cache_prefix . $group;
			$this->cache->save($phrases, $id);
		}
	}

	

	/**
	 * Invalidate a group of phrases in the cache.
	 *
	 * @param string $group
	 */
	public function invalidateCachedGroup($group)
	{
		if (!$this->cache) {
			throw new \RuntimeException('No cache object was set, cannot invalidate');
		}
		$id = $this->cache_prefix . $group;

		$this->cache->remove($id);
	}
}