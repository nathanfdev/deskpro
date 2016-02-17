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

namespace DpRun;

class BuildFinder
{
    /**
     * @var string
     */
    private $baseapp_dir;

    /**
     * Array of build_id=>dirname
     * @var array
     */
    private $available_builds;

    /**
     * @var \DpRun\ConfigReader
     */
    private $config_reader;

    /**
     * ActiveBuildFinder constructor.
     *
     * @param ConfigReader $config_reader
     * @param string       $baseapp_dir
     */
    public function __construct(ConfigReader $config_reader, $baseapp_dir)
    {
        $this->config_reader = $config_reader;
        $this->baseapp_dir   = $baseapp_dir;
    }

    /**
     * @return array Array of build_id=>dirname
     */
    private function findAvailableBuilds()
    {
        if ($this->available_builds !== null) {
            return $this->available_builds;
        }

        $iter   = new \FilesystemIterator($this->baseapp_dir);
        $builds = [];

        /** @var \SplFileInfo $f */
        foreach ($iter as $f) {
            if ($f->isDir() && $f->getBasename() !== 'run') {
                $time_file = $f->getRealPath().'/sys/config/build-time.txt';
                if (file_exists($time_file)) {
                    $time          = intval(trim(file_get_contents($time_file)));
                    $builds[$time] = $f->getBasename();
                }
            }
        }

        // Fallback on dev build if it exists
        if (!$builds && is_dir($this->baseapp_dir.'/BUILD')) {
            $builds[time()] = 'BUILD';
        }

        if (!$builds) {
            throw new \RuntimeException('There are no builds available in: ' . $this->baseapp_dir);
        }

        ksort($builds, SORT_NUMERIC);

        return $this->available_builds = $builds;
    }

    /**
     * @return string
     */
    public function getLatestBuildDir()
    {
        $builds = $this->findAvailableBuilds();
        end($builds);
        return current($builds);
    }

    /**
     * This will get the active build. The active build is usually stored in a cache
     * file $active_build_file so this is a very fast op.
     *
     * However, when performing an upgrade, a special trigger file is put in place ($active_build_file.updating)
     * which tells us to connect to the db and do a check to determine the real build.
     *
     * So for a short time during an uodate, every pageload will be doing 'extra work' to connect to the db to check the
     * current build.
     *
     * Note: During development and testing, the build is always BUILD.
     *
     * @param string $active_build_file Path to the file that contains the current build name
     * @param bool   $force_update
     * @return int|string
     */
    public function getActiveBuildDir($active_build_file, $force_update = false)
    {
        if (
            $this->config_reader->getConfig('env.environment') === 'dev'
            || $this->config_reader->getConfig('env.environment') === 'test'
        ) {
            return 'BUILD';
        }

        if (file_exists($active_build_file)) {
            $exist_build_dir = trim(file_get_contents($active_build_file));
        } else {
            $exist_build_dir = 0;
        }

        // - When an updating is being installed, we write a trigger file
        // that represents that the build ID will be changing imminently
        // - This causes this loader to always check the db for the
        // version info (ie doesnt trust the cache).
        // - Then when the version is finally switched, we go back
        // to using the cache file like normal
        // - This helps with multi-server setups where it's a two-step
        // process: install new build files, then run upgrade script
        $is_updating = $force_update || file_exists($active_build_file.'.updating');

        // Have the cached dirname
        if ($exist_build_dir && !$is_updating && !$force_update) {
            return $exist_build_dir;
        }

        $db_build_id = $this->getDbBuildId();

        if ($db_build_id) {
            $build_dir = $this->selectDirForBuildId($db_build_id);
        } else if ($exist_build_dir) {
            $build_dir = $exist_build_dir;
        } else {
            $build_dir = $this->getLatestBuildDir();
        }

        if ($build_dir != $exist_build_dir) {
            @file_put_contents($active_build_file, $build_dir);
            @unlink($active_build_file . '.updating');
        }

        return $build_dir;
    }

    /**
     * @param int $build_id
     * @return string
     */
    private function selectDirForBuildId($build_id)
    {
        $last = null;
        foreach ($this->findAvailableBuilds() as $time => $dirname) {
            if ($time > $build_id) {
                break;
            }
            $last = $dirname;
        }

        if (!$last) {
            $last = $this->getLatestBuildDir();
        }

        return $last;
    }

    /**
     * Will attempt to connect to db to find the version
     *
     * @return int
     */
    private function getDbBuildId()
    {
        // this is a low util, we might not have even checked for PDO ext yet
        if (!class_exists('PDO', false)) {
            return 0;
        }

        require_once __DIR__.'/LowUtil.php';
        $db_config = $this->config_reader->getConfig('database');

        // no config info
        if (!($db_config && !empty($db_config['user']) && !empty($db_config['password']))) {
            return 0;
        }

        $dbinfo = LowUtil::getMysqlInfoFromConfigArray($db_config);

        try {
            $pdo = new \PDO($dbinfo['dsn'], $dbinfo['user'], $dbinfo['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $build_id = $pdo->query("SELECT value FROM settings WHERE name = 'core.deskpro_build' LIMIT 1")->fetchColumn();
            $pdo = null;

            return $build_id ?: 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
