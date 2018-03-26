<?php

namespace DpRun;

class BuildFinder
{
    /**
     * @var string
     */
    private $baseAppDir;

    /**
     * @var \DpRun\ConfigReader
     */
    private $configReader;

    /**
     * @var BuildScanner
     */
    private $buildScanner;

    /**
     * ActiveBuildFinder constructor.
     *
     * @param ConfigReader $configReader
     * @param string       $baseAppDir
     */
    public function __construct(ConfigReader $configReader, $baseAppDir)
    {
        $this->configReader = $configReader;
        $this->baseAppDir   = $baseAppDir;
        $this->buildScanner = new BuildScanner($this->baseAppDir);
    }

    /**
     * @return string
     */
    public function getLatestBuildDir()
    {
        return $this->buildScanner->getLatestBuildDir();
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
     * @param string $activeBuildFile Path to the file that contains the current build name
     * @param bool   $forceUpdate
     * @return int|string
     */
    public function getActiveBuildDir($activeBuildFile, $forceUpdate = false)
    {
        if (
            $this->configReader->getConfig('env.environment') === 'dev'
            || $this->configReader->getConfig('env.environment') === 'test'
        ) {
            return 'BUILD';
        }

        if (file_exists($activeBuildFile)) {
            $existBuildDir = trim(file_get_contents($activeBuildFile));

            // The active build dir no longer exists, so we need to re-scan
            if (!is_dir($this->baseAppDir.DIRECTORY_SEPARATOR.$existBuildDir)) {
                $existBuildDir = 0;
            }
        } else {
            $existBuildDir = 0;
        }

        // - When an updating is being installed, we write a trigger file
        // that represents that the build ID will be changing imminently
        // - This causes this loader to always check the db for the
        // version info (ie doesnt trust the cache).
        // - Then when the version is finally switched, we go back
        // to using the cache file like normal
        // - This helps with multi-server setups where it's a two-step
        // process: install new build files, then run upgrade script
        $is_updating = $forceUpdate || file_exists($activeBuildFile.'.updating');

        // Have the cached dirname
        if ($existBuildDir && !$is_updating && !$forceUpdate) {
            return $existBuildDir;
        }

        $dbBuildId = $this->getDbBuildId();

        if ($dbBuildId) {
            $buildDir = $this->selectDirForBuildId($dbBuildId);
        } else if ($existBuildDir) {
            $buildDir = $existBuildDir;
        } else {
            $buildDir = $this->getLatestBuildDir();
        }

        if ($buildDir !== $existBuildDir) {
            @file_put_contents($activeBuildFile, $buildDir);
            @unlink($activeBuildFile . '.updating');
        }

        return $buildDir;
    }

    /**
     * @param int $buildId
     * @return string
     */
    private function selectDirForBuildId($buildId)
    {
        $last = null;
        foreach ($this->buildScanner->getAvailableBuilds() as $time => $dirname) {
            if ($time > $buildId) {
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
        $dbConfig = $this->configReader->getConfig('database');

        // no config info
        if (!($dbConfig && !empty($dbConfig['user']) && !empty($dbConfig['password']))) {
            return 0;
        }

        $dbinfo = LowUtil::getMysqlInfoFromConfigArray($dbConfig);

        try {
            $pdo = new \PDO($dbinfo['dsn'], $dbinfo['user'], $dbinfo['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $buildId = $pdo->query("SELECT value FROM settings WHERE name = 'core.deskpro_build' LIMIT 1")->fetchColumn();
            $pdo = null;

            return $buildId ?: 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
