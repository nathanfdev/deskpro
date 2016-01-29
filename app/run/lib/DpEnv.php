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

require_once __DIR__.'/DpRun/ConfigReader.php';

/**
 * This loader file is the first thing run on any DeskPRO app file.
 * It determines app paths:.
 */
class DpEnv
{
    /**
     * @var string
     */
    private $dp_root;

    /**
     * @var string
     */
    private $config_dir;

    /**
     * @var string
     */
    private $var_dir;

    /**
     * @var string
     */
    private $www_dir;

    /**
     * @var string
     */
    private $baseapp_dir;

    /**
     * @var string
     */
    private $active_build;

    /**
     * @var string
     */
    private $app_dir;

    /**
     * @var string
     */
    private $appcache_dir;

    /**
     * @var string
     */
    private $appcache_shared_dir;

    /**
     * @var string prod, dev or test
     */
    private $env_id;

    /**
     * @var \DpRun\ConfigReader
     */
    private $config_reader;

    public function __construct()
    {
        $this->dp_root     = realpath(__DIR__.'/../');
        $this->baseapp_dir = $this->dp_root.DIRECTORY_SEPARATOR.'app';
        $this->config_dir  = $this->resolveCustomPath($this->dp_root.DIRECTORY_SEPARATOR.'config');
        $this->var_dir     = $this->resolveCustomPath($this->dp_root.DIRECTORY_SEPARATOR.'var');
        $this->www_dir     = $this->resolveCustomPath($this->dp_root.DIRECTORY_SEPARATOR.'www');

        $this->config_reader = new \DpRun\ConfigReader([$this->config_dir]);

        $this->active_build = $this->resolveActiveBuild(
            $this->baseapp_dir,
            $this->var_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'active_build.txt'
        );

        $this->env_id = $this->config_reader->getConfig('env.environment', 'prod') ?: 'prod';

        $this->app_dir             = $this->baseapp_dir.DIRECTORY_SEPARATOR.$this->active_build;
        $this->appcache_dir        = $this->var_dir.DIRECTORY_SEPARATOR.'appcache'.DIRECTORY_SEPARATOR.$this->active_build.DIRECTORY_SEPARATOR.$this->env_id;
        $this->appcache_shared_dir = $this->var_dir.DIRECTORY_SEPARATOR.'appcache'.DIRECTORY_SEPARATOR.$this->active_build.DIRECTORY_SEPARATOR.'shared';
    }

    /**
     * If the user places a custom_path.txt file inside of a sys dir,
     * they can override it's default location.
     *
     * @param string $path
     *
     * @return string
     */
    private function resolveCustomPath($path)
    {
        $path_name = basename($path);

        if ($custom_path = $this->config_reader->getConfig('paths.dp_paths.'.$path_name)) {
            return $custom_path;
        }

        return realpath($path);
    }

    /**
     * @param string $baseapp_dir
     * @param string $active_build_file
     *
     * @return string
     */
    private function resolveActiveBuild($baseapp_dir, $active_build_file)
    {
        if ($this->config_reader->getConfig('env.environment') === 'dev') {
            return 'BUILD';
        }

        if (file_exists($active_build_file)) {
            $exist_build = trim(file_get_contents($active_build_file));
        } else {
            $exist_build = 0;
        }

        // - When an updating is being installed, we write a trigger file
        // that represents that the build ID will be changing imminently
        // - This causes this loader to always check the db for the
        // version info (ie doesnt trust the cache).
        // - Then when the version is finally switched, we go back
        // to using the cache file like normal
        // - This helps with multi-server setups where it's a two-step
        // process: install new build files, then run upgrade script
        $is_updating = file_exists($active_build_file.'.updating');

        if ($exist_build && !$is_updating) {
            return $exist_build;
        }

        require_once __DIR__.'/DpRun/ActiveBuildFinder.php';
        $finder = new \DpRun\ActiveBuildFinder($this->config_reader, $baseapp_dir);

        $build = $finder->findActiveBuild();

        if ($build != $exist_build) {
            @file_put_contents($active_build_file, $build);
            @unlink($active_build_file.'.updating');
        }

        return $build;
    }

    /**
     * @return string
     */
    public function getDpRoot()
    {
        return $this->dp_root;
    }

    /**
     * @return string
     */
    public function getConfigDir()
    {
        return $this->config_dir;
    }

    /**
     * @return string
     */
    public function getVarDir()
    {
        return $this->var_dir;
    }

    /**
     * @return string
     */
    public function getWwwDir()
    {
        return $this->www_dir;
    }

    /**
     * @return string
     */
    public function getBaseAppDir()
    {
        return $this->baseapp_dir;
    }

    /**
     * @return string
     */
    public function getActiveBuild()
    {
        return $this->active_build;
    }

    /**
     * @return string
     */
    public function getAppDir()
    {
        return $this->app_dir;
    }

    /**
     * @return string
     */
    public function getAppCacheDir()
    {
        return $this->appcache_dir;
    }

    /**
     * @return string
     */
    public function getAppCacheSharedDir()
    {
        return $this->appcache_shared_dir;
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        return $this->var_dir.DIRECTORY_SEPARATOR.'tmp';
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->var_dir.DIRECTORY_SEPARATOR.'cache';
    }

    /**
     * @return string
     */
    public function getLogsDir()
    {
        return $this->var_dir.DIRECTORY_SEPARATOR.'logs';
    }

    /**
     * @return string
     */
    public function getDebugDir()
    {
        return $this->var_dir.DIRECTORY_SEPARATOR.'debug';
    }

    /**
     * prod, dev or test.
     *
     * @return string
     */
    public function getEnvId()
    {
        return $this->env_id;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->getEnvId() === 'dev' || $this->getEnvId() === 'test';
    }
}
