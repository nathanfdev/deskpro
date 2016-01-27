<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle\DataSet;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
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
     * @var EntityManager
     */
    private $em;

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
     * @param EntityManager      $em
     * @param Connection         $db
     * @param string             $cache_dir
     * @param string             $mysql_bin_path
     * @param string             $mysqldump_bin_path
     */
    public function __construct(ContainerInterface $container, EntityManager $em, Connection $db, $cache_dir, $mysql_bin_path = 'mysql', $mysqldump_bin_path = 'mysqldump')
    {
        $this->container          = $container;
        $this->em                 = $em;
        $this->db                 = $db;
        $this->cache_dir          = $cache_dir;
        $this->mysql_bin_path     = $mysql_bin_path;
        $this->mysqldump_bin_path = $mysqldump_bin_path;
    }

    /**
     * @return \Application\DeskPRO\DBAL\Connection
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @return DeskproContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return int Number of tables dropped
     */
    private function clearDatabase()
    {
        $this->getDb()->exec("DROP DATABASE IF EXISTS {$this->getDatabaseName()}");
        $this->getDb()->exec("CREATE DATABASE {$this->getDatabaseName()}");
        $this->getDb()->exec("USE {$this->getDatabaseName()}");

        // Clear the ORM
        $this->getEm()->clear();

        return 1;
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
        if (strlen(DP_DATABASE_PASSWORD)) {
            $cmd = sprintf(
                '%s --opt -Q -h%s --port=%s -u%s -p%s %s > %s',
                $this->mysqldump_bin_path,
                escapeshellarg(DP_DATABASE_HOST),
                escapeshellarg(3306),
                escapeshellarg(DP_DATABASE_USER),
                escapeshellarg(DP_DATABASE_PASSWORD),
                escapeshellarg($this->getDatabaseName()),
                escapeshellarg($this->getCachePath())
            );
        } else {
            $cmd = sprintf(
                '%s --opt -Q -h%s --port=%s -u%s %s > %s',
                $this->mysqldump_bin_path,
                escapeshellarg(DP_DATABASE_HOST),
                escapeshellarg(3306),
                escapeshellarg(DP_DATABASE_USER),
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
            throw new \RuntimeException(print_r(array($cmd, $out), 1));
        }
    }

    /**
     * Installs the set from the cached SQL.
     *
     * @return bool
     */
    private function installFromCache()
    {
        if (strlen(DP_DATABASE_PASSWORD)) {
            $cmd = sprintf(
                '%s -h%s -u%s -p%s %s < %s',
                $this->mysql_bin_path,
                escapeshellarg(DP_DATABASE_HOST),
                escapeshellarg(DP_DATABASE_USER),
                escapeshellarg(DP_DATABASE_PASSWORD),
                escapeshellarg($this->getDatabaseName()),
                escapeshellarg($this->getCachePath())
            );
        } else {
            $cmd = sprintf(
                '%s -h%s -u%s %s < %s',
                $this->mysql_bin_path,
                escapeshellarg(DP_DATABASE_HOST),
                escapeshellarg(DP_DATABASE_USER),
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
            throw new \RuntimeException(print_r(array($cmd, $out), 1));
        }
    }

    /**
     * Installs this db set.
     */
    public function install()
    {
        $this->clearDatabase();
        if ($this->isCached()) {
            $this->installFromCache();
        } else {
            $this->installDatabase();
            $this->installSet();

            // This is required or else some e2e tests
            // might fail early because it thinks the isntall failed
            $this->getDb()->insert('settings', [
                'name'  => 'installer.done',
                'value' => 1,
            ]);

            if ($this->cache_dir) {
                $this->dumpToCache();
            }
        }
    }

    /**
     * Installs a fresh DeskPRO database with the bare data to make it a functional install.
     *
     * @return int The number of queries executed
     */
    private function installDatabase()
    {
        $base_schema_cache = null;
        if ($this->cache_dir) {
            $base_schema_cache = $this->cache_dir.'/base_schema.php';
        }

        if ($base_schema_cache && file_exists($base_schema_cache)) {
            $queries = require $base_schema_cache;
        } else {
            $gs      = new \Application\InstallBundle\Data\GenerateSchema($this->getEm());
            $queries = array(
                'creates' => $gs->getCreates(),
                'alters'  => $gs->getAlters(),
            );

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

        // Manually create install_data
        // Its used by the installer to test that we have create perms, so its
        // not part of the schema
        $this->getDb()->exec("
            CREATE TABLE `install_data` (
              `build` varchar(30) NOT NULL,
              `name` varchar(75) NOT NULL DEFAULT '',
              `data` blob NOT NULL,
              PRIMARY KEY (`build`,`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ");
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
    private function getDatabaseName()
    {
        return DP_DATABASE_NAME;
    }

    /**
     * Install data specific to this set.
     *
     * @return int
     */
    abstract protected function installSet();
}
