<?php

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle\DataSet;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Connection;
use Orb\Util\Util;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AbstractDbSet.
 */
abstract class AbstractDbSet implements DataSetInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var string
     */
    private $cache_dir;

    /**
     * @var string
     */
    private $mysql_bin_path;

    /**
     * @var string
     */
    private $mysqldump_bin_path;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param Connection         $db
     * @param string             $cache_dir
     * @param string             $mysql_bin_path
     * @param string             $mysqldump_bin_path
     */
    public function __construct(ContainerInterface $container, Connection $db, $cache_dir, $mysql_bin_path = 'mysql', $mysqldump_bin_path = 'mysqldump')
    {
        $this->container          = $container;
        $this->db                 = $db;
        $this->cache_dir          = $cache_dir;
        $this->mysql_bin_path     = $mysql_bin_path;
        $this->mysqldump_bin_path = $mysqldump_bin_path;
    }

    /**
     * @param string $type
     *
     * @return \Application\DeskPRO\DBAL\Connection
     */
    public function getDb($type = 'default')
    {
        return $this->container->get(sprintf('doctrine.dbal.%s_connection', $type));
    }

    /**
     * @param string $type
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm($type = 'default')
    {
        return $this->container->get(sprintf('doctrine.orm.%s_entity_manager', $type));
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getSystemEm()
    {
        return $this->getEm('system');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getAuditEm()
    {
        return $this->getEm('audit');
    }

    /**
     * @return DeskproContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $fname
     * @param string $lname
     * @param string $email
     * @param string $pass
     * @param bool   $agent
     * @param bool   $admin
     * @param bool   $is_deleted
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    protected function addUser($fname, $lname, $email, $pass, $agent = false, $admin = false, $is_deleted = false)
    {
        $new_user             = new \Application\DeskPRO\Entity\Person();
        $new_user->first_name = $fname;
        $new_user->last_name  = $lname;
        $new_user->setEmail($email, true);
        $new_user->setPassword($pass);
        $new_user->is_user      = true;
        $new_user->is_confirmed = true;
        $new_user->is_deleted   = $is_deleted;

        if ($agent || $admin) {
            $new_user->is_agent  = true;
            $new_user->can_agent = true;
        }

        if ($admin) {
            $new_user->can_admin   = true;
            $new_user->can_billing = true;
            $new_user->can_reports = true;
        }

        $this->getEm()->persist($new_user);
        $this->getEm()->flush();

        return $new_user;
    }

    /**
     * Get the cache name for this set.
     *
     * @return string
     */
    private function getCacheName()
    {
        return Util::getBaseClassname($this);
    }

    /**
     * Get the cache file path for this set.
     *
     * @return string
     */
    private function getCachePath()
    {
        if (!$this->cache_dir) {
            throw new \RuntimeException('No cache directory is set');
        }

        return $this->cache_dir.DIRECTORY_SEPARATOR.$this->getCacheName();
    }

    /**
     * @return bool
     */
    private function isCached()
    {
        if ($this->cache_dir && file_exists($this->getCachePath())) {
            return true;
        }

        return false;
    }

    /**
     * Dumps the database to the cache file.
     */
    private function dumpToCache()
    {
        $modifiers = '--no-create-info --skip-triggers --extended-insert --lock-tables --quick';
        if (strlen($GLOBALS['DP_ENV']->getConfig('database.password'))) {
            $cmd = sprintf(
                "%s --opt -Q -h%s --port=%s -u%s -p%s %s $modifiers > %s",
                $this->mysqldump_bin_path,
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.host')),
                escapeshellarg(3306),
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.user')),
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.password')),
                escapeshellarg($this->getDatabaseName()),
                escapeshellarg($this->getCachePath())
            );
        } else {
            $cmd = sprintf(
                "%s --opt -Q -h%s --port=%s -u%s %s $modifiers > %s",
                $this->mysqldump_bin_path,
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.host')),
                escapeshellarg(3306),
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.user')),
                escapeshellarg($this->getDatabaseName()),
                escapeshellarg($this->getCachePath())
            );
        }

        $cmd .= ' 2>&1';
        $ret = 0;
        exec($cmd, $out, $ret);

        if ($ret) {
            echo "Command Failed: $cmd\n";
            echo implode("\n", $out);
            throw new \RuntimeException(print_r([$cmd, $out], 1));
        }
    }

    /**
     * Installs the set from the cached SQL.
     *
     * @return bool
     */
    private function installFromCache()
    {
        if (strlen($GLOBALS['DP_ENV']->getConfig('database.password'))) {
            $cmd = sprintf(
                '%s -h%s -u%s -p%s %s < %s',
                $this->mysql_bin_path,
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.host')),
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.user')),
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.password')),
                escapeshellarg($this->getDatabaseName()),
                escapeshellarg($this->getCachePath())
            );
        } else {
            $cmd = sprintf(
                '%s -h %s -u %s %s < %s',
                $this->mysql_bin_path,
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.host')),
                escapeshellarg($GLOBALS['DP_ENV']->getConfig('database.user')),
                escapeshellarg($this->getDatabaseName()),
                escapeshellarg($this->getCachePath())
            );
        }

        $cmd .= ' 2>&1';
        $ret = 0;
        exec($cmd, $out, $ret);

        if ($ret) {
            echo "Command Failed: $cmd\n";
            echo implode("\n", $out);
            throw new \RuntimeException(print_r([$cmd, $out], 1));
        }
    }

    private static $isStructureCreated = false;

    /**
     * {@inheritdoc}
     */
    public function install($recreateStructure = false)
    {
        if (!self::$isStructureCreated || $recreateStructure) {
            $this->getDb()->exec("DROP DATABASE IF EXISTS {$this->getDatabaseName()}");
            $this->getDb()->exec("CREATE DATABASE {$this->getDatabaseName()}");
            $this->getDb()->exec("USE {$this->getDatabaseName()}");
            $this->installDatabase('default', true);
            $this->installDatabase('system');
            $this->installDatabase('audit');

            self::$isStructureCreated = true;
        } else {
            $this->clearDb();
        }

        if ($this->isCached()) {
            $this->installFromCache();
        } else {
            $this->installSet();

            if ($this->cache_dir) {
                $this->dumpToCache();
            }
        }

        $this->getEm()->clear();
    }

    /**
     * Clear DB tables.
     */
    private function clearDb()
    {
        $purger = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        foreach (['default', 'system', 'audit'] as $dbType) {
            $this->getDb($dbType)->exec('SET FOREIGN_KEY_CHECKS = 0;');
            $purger->setEntityManager($this->getEm($dbType));
            $purger->purge();
            $this->getDb($dbType)->exec('SET FOREIGN_KEY_CHECKS = 1;');
        }
    }

    /**
     * Installs a fresh DeskPRO database with the bare data to make it a functional install.
     *
     * @param string $em_name
     * @param bool   $is_master_schema
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     *
     * @return int The number of queries executed
     */
    private function installDatabase($em_name, $is_master_schema = false)
    {
        $base_schema_cache = null;
        if ($this->cache_dir) {
            $base_schema_cache = $this->cache_dir."/{$em_name}_schema.php";
        }

        if ($base_schema_cache && file_exists($base_schema_cache)) {
            $queries = require $base_schema_cache;
        } else {
            $em      = $this->container->get("doctrine.orm.{$em_name}_entity_manager");
            $gs      = new \Application\InstallBundle\Data\GenerateSchema($em, $is_master_schema);
            $queries = [
                'creates' => $gs->getCreates(),
                'alters'  => $gs->getAlters(),
            ];

            if (!is_dir($this->cache_dir)) {
                $fs = new Filesystem();
                $fs->mkdir($this->cache_dir);
            }

            if (!file_exists($base_schema_cache)) {
                touch($base_schema_cache);
            }

            if ($base_schema_cache) {
                file_put_contents(
                    $base_schema_cache,
                    '<?php return '.var_export($queries, true).";\n"
                );
            }
        }

        $count = 1;
        foreach ($queries['creates'] as $q) {
            ++$count;
            $this->getDb()->exec($q);
        }

        foreach ($queries['alters'] as $q) {
            ++$count;
            $this->getDb()->exec($q);
        }

        return $count;
    }

    /**
     * @return string
     */
    protected function getDatabaseName()
    {
        return $GLOBALS['DP_ENV']->getConfig('database.dbname');
    }

    /**
     * @return string
     */
    protected function getLicenseKey()
    {
        return file_get_contents(DP_DIR.'/dev/dev-lic-key.txt');
    }

    /**
     * Install data specific to this set.
     *
     * @return int
     */
    abstract protected function installSet();
}
