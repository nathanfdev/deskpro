<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Doctrine
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Doctrine\Common\Cache;

use \Orb\Util\Util;

/**
 * The array file cache is a cache that writes k=>v to a file on the filesystem.
 * This is very ineffecient for writes, but can work well if writes are very rare (ie part of a build system).
 */
class ArrayFileCache extends \Doctrine\Common\Cache\CacheProvider
{
	/**
	 * Data is array(key => array(time => timestamp, data => data, deleted => true, updated => true)
	 *
     * @var array $data
     */
    protected $data = null;

	/**
	 * Do we have unwritten changes?
	 *
	 * @var bool
	 */
	protected $dirty = false;

	/**
	 * @var string
	 */
	protected $cache_file;

	/**
	 * Automatically write after each update
	 *
	 * @var bool
	 */
	protected $auto_write = false;


	/**
	 * @param string $cache_file
	 */
	public function __construct($cache_file)
	{
		$this->cache_file = $cache_file;
	}


	/**
	 * Register a shutdown function to save the cache on exit if there are changes
	 *
	 * @return mixed
	 */
	public function registerShutdownCommit()
	{
		static $has_reg = false;
		if ($has_reg) {
			return;
		}

		$has_reg = true;

		register_shutdown_function(array($this, 'commitIfDirty'));
	}


	/**
	 * Reload all data
	 */
	public function reloadData()
	{
		if ($this->data === null) {
			$this->data = array();
		}

		if (file_exists($this->cache_file)) {
			$load_data = require($this->cache_file);

			$time = time();

			foreach ($load_data as $k => $info) {
				if ($info['die'] > $time) {
					continue;
				}

				// We dont have the item, add it
				if (!isset($this->data[$k])) {
					$this->data[$k] = $info;

				// The item in the file is newer
				} elseif ($info['time'] > $this->data[$k]['time']) {
					$this->data[$k] = $info;
				}
			}
		}
	}


    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
		if ($this->data === null) $this->reloadData();

		if (isset($this->data[$id]) && !isset($this->data[$id]['deleted']) && (!$this->data[$id]['die'] || $this->data[$id]['die'] < time())) {
			if (isset($this->data[$id]['serialized'])) {
				if (isset($this->data[$id]['data_u'])) {
					return $this->data[$id]['data_u'];
				}

				$this->data[$id]['data_u'] = unserialize($this->data[$id]['data']);
				return $this->data[$id]['data_u'];
			} else {
				return $this->data[$id]['data'];
			}
		}

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
		if ($this->data === null) $this->reloadData();

        if (isset($this->data[$id]) && !isset($this->data[$id]['deleted']) && (!$this->data['die'] || $this->data['die'] < time())) {
			return true;
		}

		return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
		if ($this->data === null) $this->reloadData();

		if (is_scalar($data)) {
			$this->data[$id] = array(
				'time' => time(),
				'die' => ($lifeTime ? time() + $lifeTime : 0),
				'data' => $data,
				'updated' => true
			);
		} else {
			$this->data[$id] = array(
				'time' => time(),
				'die' => ($lifeTime ? time() + $lifeTime : 0),
				'data' => serialize($data),
				'data_u' => $data,
				'serialized' => true,
				'updated' => true
			);
		}

		$this->dirty = true;

		if ($this->auto_write) {
			$this->commit();
		}

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
		if ($this->data === null) $this->reloadData();

        $this->data[$id] = array(
			'time' => time(),
			'die' => 0,
			'data' => 0,
			'deleted' => true
		);

		$this->dirty = true;

		if ($this->auto_write) {
			$this->commit();
		}

        return true;
    }

	/**
	 * Save the current cache to disk
	 *
	 * @return int
	 */
	public function commit()
	{
		// Always reload
		$this->reloadData();

        $result = array();
		$time = time();
		foreach ($this->data as $k => $v) {
			if (!isset($v['deleted']) && (!$v['die'] || $v['die'] < $time)) {
				unset($v['deleted'], $v['updated'], $v['data_u']);
				$result[$k] = $v;
			}
		}

		$this->data = $result;

		$php = "<?php\nreturn " . var_export($result, true) . ";\n";

		$this->dirty = false;

		file_put_contents($this->cache_file, $php);
		file_put_contents($this->cache_file, php_strip_whitespace($this->cache_file));
	}


	/**
	 * Commit if there have been changes to the cache
	 */
	public function commitIfDirty()
	{
		if ($this->dirty) {
			$this->commit();
		}
	}


    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
		$this->dirty = false;

		$this->data = array();
		$php = "<?php return array(); ";
		return file_put_contents($this->cache_file, $php);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        return null;
    }
}
