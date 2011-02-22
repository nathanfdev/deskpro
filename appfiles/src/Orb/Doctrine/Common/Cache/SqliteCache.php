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
 * The SQLite cache driver stores cache info in an sqlite db, either in the
 * filesystem or memory.
 *
 * You can re-use one connection for multiple caches if you specify a $connection_name
 * with setDbFile(), just cahnge $cache_name. This essentially creates a new table
 * for each cache, so you can reuse one db.
 */
class SqliteCache extends \Doctrine\Common\Cache\AbstractCache
{
	/**
	 * An array of saved connections. Allows us to reuse connections.
	 * @var Doctrine\DBAL\Connection[]
	 */
	protected static $named_connections = array();

	/**
	 * The path to the DB file, or 'MEMORY' if its memory.
	 */
	protected $_dbfile;

	/**
	 * The cache name, aka the table in the database
	 * @var string
	 */
	protected $_cache_name = 'doctrine_cache';

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $_db;

	/**
	 * Sets the SQLite database connection to use for the cache. This will automatically
	 * initialize the database/tables if it doesn't exist.
	 *
	 * If $filepath is 'MEMORY', then the SQLite database will be created in memory. (Note that
	 * this is mainly a debug tool; memory tables are destroyed as soon as the connection dies.)
	 *
	 * $cache_name is the name of the cache table. $connection_name is the named connection for this
	 * sqlite database. You can reuse connections for multiple caches, just change the $cache_name
	 * so they use different tables.
	 *
	 * @param string $filepath
	 * @param string $cache_name
	 * @param string|null $connection_name
	 * @return Doctrine\DBAL\Connection
	 */
	public function setDbFile($filepath, $cache_name = 'doctrine_cache', $connection_name = null)
	{
		$this->_dbfile = $filepath;
		$this->_cache_name = $cache_name;

		if ($connection_name !== null) {
			if (isset(self::$named_connections[$connection_name])) {
				$this->_db = self::$named_connections[$connection_name];
			}
		}
		if (!$this->_db) {
			$params = array(
				'driver' => 'pdo_sqlite'
			);

			if ($filepath == 'MEMORY') {
				$params['memory'] = true;
			} else {
				$params['path'] = $filepath;
			}

			$this->_db = \Doctrine\DBAL\DriverManager::getConnection($params);

			if ($connection_name) {
				self::$named_connections[$connection_name] = $this->_db;
			}
		}

		$exists = $this->_db->fetchColumn("SELECT name FROM sqlite_master WHERE type='table' AND name='{$cache_name}'");
		if (!$exists) {
			$this->_db->exec("CREATE TABLE {$cache_name} (id TEXT PRIMARY KEY, data BLOB, expire INTEGER)");
		} else {
			if (mt_rand(1,10) <= 3) {
				$this->_db->executeUpdate("DELETE FROM {$cache_name} WHERE expire < ? AND expire > 0", array(time()));
			}
		}

		return $this->_db;
	}



	/**
	 * Get the database connection
	 *
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getDbConnection()
	{
		return $this->_db;
	}



	public function getIds()
	{
		$keys = array();
		foreach ($this->_db->fetchAll("SELECT id FROM {$this->_cache_name} WHERE (expire = 0 OR expire > ?)", array(time())) as $x) {
			$keys[] = $x['id'];
		}

		return $keys;
	}

	protected function _doFetch($id)
    {
        $data = $this->_db->fetchColumn("SELECT data FROM {$this->_cache_name} WHERE id = ? AND (expire = 0 OR expire > ?)", array($id, time()));
		$data = unserialize($data);
		return $data;
    }

    protected function _doContains($id)
    {
        $exists = $this->_db->fetchColumn("SELECT id FROM {$this->_cache_name} WHERE id = ? AND (expire = 0 OR expire > ?)", array($id, time()));

		return (bool)$exists;
    }

    protected function _doSave($id, $data, $lifeTime = 0)
    {
		$expire = 0;
		if ($lifeTime) {
			$expire = time() + $lifeTime;
		}

		$data = serialize($data);
        $this->_db->executeUpdate("INSERT OR REPLACE INTO {$this->_cache_name} (id, data, expire) VALUES (?, ?,?)", array($id, $data, $expire));

		return true;
    }

    protected function _doDelete($id)
    {
		$this->_db->executeUpdate("DELETE FROM {$this->_cache_name} WHERE id = ?", array($id));

		return true;
    }
}