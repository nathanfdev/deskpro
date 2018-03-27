<?php

namespace DpRun;

require_once __DIR__ . '/ConfigReaderInterface.php';
require_once __DIR__ . '/ConfigReader.php';

require_once __DIR__ . '/DatManagerInterface.php';
require_once __DIR__ . '/DatManager.php';

require_once __DIR__ . '/BuildScanner.php';
require_once __DIR__ . '/BuildFinder.php';

/**
 * This loader file is the first thing run on any DeskPRO app file.
 * It determines app paths and the runtime environment.
 *
 * === Important: app vs user dirs ===
 *
 * It's very important that you never store instance-specific data in app dirs (e.g., app cache).
 * Read below, but its critical that per-instance data is always written to a path with 'user' in it's name.
 * E.g., getAppCacheDir() cannot contain user data, but getUserCacheDir() can.
 *
 * Read more below, but briefly: This is important to support cloud where all sites use 1 instance of files.
 *
 * === Structure ===
 *
 * <code>
 * /deskpro                  (SG) The main DeskPRO instance
 *     |- /app               (SG) base app dir
 *         |- /BUILD              these are app dirs, specific versions of deskpro
 *         |- /512
 *         |- /513
 *     |- /attachments       (DU) default location to store blobs [id:files]
 *     |- /backups           (DU) default location for backups [id:files]
 *     |- /bin               (..) This just contains shortcuts to a real builds /bin dir
 *     |- /config            (SG) Where config is read from. Its considered 'global' because typically
 *                                config values are loaded differnly with other scenarios like cloud.
 *     |- /var               (D.) Non-critical data (cache/logs). [id:var]
 *         |- /cache         (DU) Where cache files are written to [id:cache]
 *             |- /build_cache    Per-build caches
 *         |- /debug         (DU) Where debug info can be written to (e.g., output of tests) [id:debug]
 *         |- /kernel_cache  (DG) Where Symfony cache files are written to [id:kernel_cache]
 *             |- /BUILD
 *             |- /512
 *             |- /513
 *         |- /logs          (DU) Where log files are written to [id:logs]
 *         |- /tmp           (DU) Where tmp files are written to [id:tmp]
 *     |- /www               (DG) Where web files exist (server doc root) [id:www]
 *         |- /assets        (SG) Asset files like CSS, JS or images etc
 *             |- /BUILD          these are app dirs, specific versions of deskpro
 *             |- /512
 *             |- /513
 * </code>
 *
 * Above you see directories marked as S or D and G or U:
 *
 * - (S)tatic directories are always the same. The paths are 'hard-coded' and don't move
 *   relative to the root of DeskPRO itself. E.g., /deskpro/app will always be the path to PHP files.
 *
 * - (D)ynamic directories are paths that can be overriden by configuration. These paths are always
 *   discovered at runtime by this DpEnv class.
 *
 * - (U)ser directories contain data that are specific to a certain instance of DeskPRO. This is an important
 *   distinction because on cloud, we have 1 set of app files for every account.
 *
 *   Note that generally, the user cache directory is probably very uncommon because we always need another way to store
 *   cache data for multi-server setups. E.g., code would need to use memcache or whatever.
 *
 * - (G)lobal directories are dirs that are expected to be used by the app. E.g., the app kernel cache used by symfony
 *   is considered nearly 'static' after build time and contains no user-specific data.
 *
 * === Overriding Paths ===
 *
 * You can override specific paths in config.paths.php by specifying a dp_paths array keyed by the IDs
 * displayed in the above tree. For example:
 *
 * <code>
 * $PATHS_CONFIG['dp_paths'] = ['kernel_cache' => '/mnt/ramdisk'];
 * </code>
 *
 * If you want to move all of 'var', then use the key 'var'. All sub-dirs will be moved as well unless
 * the sub-dir itself has a config override.
 *
 * The other common use-case (e.g. cloud) is to move all user directories under a new root.
 * We make that easy by using the special key 'user_dir':
 *
 * <code>
 * $PATHS_CONFIG['dp_paths'] = ['user_dir' => '/data/user_981'];
 * // New we have:
 * // /data/user_981
 * //     |- /attachments
 * //     |- /backups
 * //     |- /var
 * //         |- /cache
 * //         |- /debug
 * //         |- /logs
 * //         |- /tmp
 * </code>
 *
 * === !!! Warning: Dont use this directly !!! ===
 *
 * Note: Try NOT to use this class directly. Once DeskPRO has booted and the container is available,
 * use the deskpro.app_env service which is a light wrapper around this.
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
    private $root_kernel_cache_dir;

    /**
     * @var string
     */
    private $root_app_dir;

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
    private $app_base_kernel_cache_dir;

    /**
     * @var string
     */
    private $user_cache_dir;

    /**
     * @var string
     */
    private $user_debug_dir;

    /**
     * @var string
     */
    private $user_logs_dir;

    /**
     * @var string
     */
    private $user_tmp_dir;

    /**
     * @var string
     */
    private $user_files_dir;

    /**
     * @var string
     */
    private $user_backups_dir;

    /**
     * @var string prod, dev or test
     */
    private $env_id;

    /**
     * @var string
     */
    private $www_root_dir;

    /**
     * @var string
     */
    private $app_www_dir;

    /**
     * @var \DpRun\ConfigReaderInterface
     */
    private $config_reader;

    /**
     * @var \DpRun\DatManagerInterface
     */
    private $dat_manager;

    /**
     * @var array
     */
    private $runtime_vars = [];

    /**
     * DpEnv constructor.
     *
     * @param string $dp_root
     * @param array  $config  Config values that will take precedence over ones read from config files.
     * @param \DpRun\ConfigReaderInterface|null $config_reader
     * @param \DpRun\DatManagerInterface|null $dat_manager
     */
    public function __construct(
        $dp_root,
        array $config = null,
        \DpRun\ConfigReaderInterface $config_reader = null,
        \DpRun\DatManagerInterface $dat_manager = null
    )
    {
        #------------------------------
        # Static paths
        #------------------------------

        $this->dp_root = realpath($dp_root);
        $baseapp_dir = $this->dp_root.DIRECTORY_SEPARATOR.'app';
        $this->root_app_dir = $baseapp_dir;

        #------------------------------
        # Prepare config reader
        #------------------------------

        if ($config_reader) {
            $this->config_reader = $config_reader;
        } else {
            $config_dir  = $this->dp_root.DIRECTORY_SEPARATOR.'config';

            if ($config && isset($config['use_config_dir'])) {
                $config_dir = $config['use_config_dir'];
            }

            // A special file named dir.alias means to use a different config directory
            if (file_exists($config_dir.DIRECTORY_SEPARATOR.'dir.alias')) {
                $config_dir = trim(file_get_contents($config_dir.DIRECTORY_SEPARATOR.'dir.alias'));
            }

            // When E2E tests are running, we switch config dirs to the test config
            // See DpBehat\E2E\E2EContext
            if (file_exists($config_dir.DIRECTORY_SEPARATOR.'e2e_running.trigger')) {
                $config_dir = $this->dp_root.'/app/BUILD/tests/config';
            }

            // Context file can be anything
            if (file_exists($config_dir.DIRECTORY_SEPARATOR.'context.php')) {
                $context = require($config_dir.DIRECTORY_SEPARATOR.'context.php');
            } else {
                $context = [];
            }

            $this->config_reader = new \DpRun\ConfigReader([$config_dir]);
            $this->config_reader->setConfigContext($context);
        }

        if ($config) {
            $this->config_reader->addConfigLoader(function($id) use ($config) {
                return isset($config[$id]) ? $config[$id] : [];
            });
        }

        #------------------------------
        # Dynamic paths
        #------------------------------

        $sys_var_dir = $this->resolveCustomPath('var', $this->dp_root.DIRECTORY_SEPARATOR.'var');

        $user_dir = $this->config_reader->getConfig('paths.dp_paths.user_dir');
        if ($user_dir) {
            $user_dir = realpath($user_dir) ?: rtrim($user_dir, '/\\');
            $user_var_dir = $user_dir . DIRECTORY_SEPARATOR . 'var';
        } else {
            $user_dir = $this->dp_root;
            $user_var_dir = $sys_var_dir;
        }

        $kernel_cache_dir          = $this->resolveCustomPath('kernel_cache', $sys_var_dir.DIRECTORY_SEPARATOR.'kernel_cache');
        $this->root_kernel_cache_dir = $kernel_cache_dir;

        $this->user_files_dir      = $this->resolveCustomPath('attachments', $user_dir.DIRECTORY_SEPARATOR.'attachments');
        $this->user_backups_dir    = $this->resolveCustomPath('backups', $user_dir.DIRECTORY_SEPARATOR.'backups');
        $this->user_cache_dir      = $this->resolveCustomPath('cache', $user_var_dir.DIRECTORY_SEPARATOR.'cache');
        $this->user_debug_dir      = $this->resolveCustomPath('debug', $user_var_dir.DIRECTORY_SEPARATOR.'debug');
        $this->user_logs_dir       = $this->resolveCustomPath('logs', $user_var_dir.DIRECTORY_SEPARATOR.'logs');
        $this->user_tmp_dir        = $this->resolveCustomPath('tmp', $user_var_dir.DIRECTORY_SEPARATOR.'tmp');

        #------------------------------
        # Current build
        #------------------------------

        if ($buildId = $this->config_reader->getConfig('paths.active_build_dir_name')) {
            $this->active_build = $buildId;
        } else if ($buildId = $this->config_reader->getConfig('env.use_build_name')) {
            $this->active_build = $buildId;
        } else {
            $build_finder = new \DpRun\BuildFinder(
                $this->config_reader,
                $baseapp_dir
            );
            $this->active_build = $build_finder->getActiveBuildDir(
                $this->user_cache_dir . '/active_build.txt'
            );
        }

        $this->env_id = $this->config_reader->getConfig('env.environment', 'prod') ?: 'prod';

        $this->app_dir                   = $baseapp_dir.DIRECTORY_SEPARATOR.$this->active_build;
        $this->app_base_kernel_cache_dir = $kernel_cache_dir.DIRECTORY_SEPARATOR.$this->active_build;

        #------------------------------
        # Prepare dat manager
        #------------------------------

        if (!$dat_manager) {
            $datManagerClass = $this->getConfig('env.dat_manager_class', 'DpRun\DatManager');
            $dat_manager = new $datManagerClass($this->user_cache_dir);
        }

        $this->dat_manager = $dat_manager;
    }

    /**
     * If the user places a custom_path.txt file inside of a sys dir,
     * they can override it's default location.
     *
     * @param string $id             The config key to use
     * @param string $path           The default path to use
     * @return string
     */
    private function resolveCustomPath($id, $path)
    {
        if ($custom_path = $this->config_reader->getConfig('paths.dp_paths.'.$id)) {
            return realpath($custom_path) ?: rtrim($custom_path, '/\\');
        }

        return realpath($path) ?: $path;
    }

    /**
     * Gets the root DeskPRO directory. This is typically the parent of all other dirs.
     *
     * Example: /path/to/deskpro
     *
     * @return string
     */
    public function getDpRoot()
    {
        return $this->dp_root;
    }

    /**
     * Get the path to the root www dir.
     *
     * Example: /path/to/deskpro/www
     *
     * @return string
     */
    public function getWwwRoot()
    {
        if ($this->www_root_dir !== null) {
            return $this->www_root_dir;
        }

        $dir = null;
        if (defined('DESKPRO_WWW_PATH')) {
            $dir = realpath(DESKPRO_WWW_PATH);
        }

        if (!$dir) {
            $content = $this->getDatManager()->readTxtFile('www_dir', null);
            if ($content) {
                $dir = realpath($content);
            }
        }

        if (!$dir) {
            // Fallback on the www dir within the deskpro root dir
            $dir = $this->getDpRoot() . DIRECTORY_SEPARATOR . 'www';
            $dir = realpath($dir) ?: $dir;
        }

        return $this->www_root_dir = $dir;
    }

    /**
     * Get the path to the current asset dir for the active app.
     *
     * Exampel: /path/to/deskpro/www/assets/BUILD
     *
     * @return string
     */
    public function getAppWwwAssetDir()
    {
        if ($this->app_www_dir !== null) {
            return $this->app_www_dir;
        }

        return $this->app_www_dir = $this->getWwwRoot() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->getAppName();
    }

    /**
     * Gets the 'name' of the currently active build. The 'name' in this case
     * just means the directory name of the active build.
     *
     * This will depend on the current environment/instance.
     *
     * Example: 502
     *
     * @return string
     */
    public function getAppName()
    {
        return $this->active_build;
    }

    /**
     * Gets the path to the current builds app dir.
     *
     * This will depend on the current environment/instance.
     *
     * Example: /path/to/deskpro/app/502
     *
     * @return string
     */
    public function getAppDir()
    {
        return $this->app_dir;
    }

    /**
     * Gets the path to the root app builds dir.
     *
     * Example: /path/to/deskpro/app
     *
     * @return string
     */
    public function getBuildDirRoot()
    {
        return $this->root_app_dir;
    }

    /**
     * Gets the base path to the kernel cache dir.
     *
     * Example: /path/to/deskpro/var/kernel_cache
     *
     * @return string
     */
    public function getKernelCacheDirRoot()
    {
        return $this->root_kernel_cache_dir;
    }

    /**
     * Gets the path to the current builds kernel cache dir.
     *
     * The kernels decide what the inner structure looks like. Typically
     * each environment and kernel has it's own dir.
     *
     * Example: /path/to/deskpro/var/appcache/502
     *
     * @return string
     */
    public function getAppBaseKernelCacheDir()
    {
        return $this->app_base_kernel_cache_dir;
    }

    /**
     * Gets the the path to tmp directory.
     * Note that this tmp directory is shared by all builds.
     *
     * Example: /path/to/deskpro/var/tmp
     *
     * @return string
     */
    public function getUserTmpDir()
    {
        return $this->user_tmp_dir;
    }

    /**
     * Gets the path to the cache directory.
     * Note that this directory is shared by all builds.
     *
     * Example: /path/to/deskpro/var/cache
     *
     * @return string
     */
    public function getUserCacheDir()
    {
        return $this->user_cache_dir;
    }

    /**
     * Gets the path to the logs directory.
     *
     * Example: /path/to/deskpro/var/logs
     *
     * @return string
     */
    public function getUserLogsDir()
    {
        return $this->user_logs_dir;
    }

    /**
     * Gets the path to the debug directory.
     *
     * Example: /path/to/deskpro/var/debug
     *
     * @return string
     */
    public function getUserDebugDir()
    {
        return $this->user_debug_dir;
    }

    /**
     * Gets the path to the backups dir.
     *
     * Example: /path/to/deskpro/backups
     *
     * @return string
     */
    public function getUserBackupsDir()
    {
        return $this->user_backups_dir;
    }

    /**
     * Gets the path to the files dir.
     *
     * Example: /path/to/deskpro/attachments
     *
     * @return string
     */
    public function getUserFilesDir()
    {
        return $this->user_files_dir;
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
        // '--no-debug' console option should override 'dev' and 'test' mode
        if ($this->getConfig('env.no_debug')) {
            return false;
        }

        return $this->getEnvId() === 'dev' || $this->getEnvId() === 'test' || $this->getConfig('env.debug_mode');
    }

    /**
     * Resets config cache. Next call to fetch config fill result in a reload.
     */
    public function resetConfigCache()
    {
        $this->config_reader->resetCache();
    }

    /**
     * @param string  $id        The config value you want
     * @param mixed   $default   If the value is unset, the default value
     * @return mixed
     */
    public function getConfig($id, $default = null)
    {
        return $this->config_reader->getConfig($id, $default);
    }

    /**
     * @return ConfigReaderInterface
     */
    public function getConfigReader()
    {
        return $this->config_reader;
    }

    /**
     * Find a config file or null if the file doesnt exist.
     *
     * @param string $f
     * @return null|string
     */
    public function findConfigFile($f)
    {
        return $this->config_reader->findConfigFile($f);
    }

    /**
     * @return \DpRun\DatManagerInterface
     */
    public function getDatManager()
    {
        return $this->dat_manager;
    }

    /**
     * Set a runtime var. Note that using this should normally be avoided
     * if possible because they are little better than simply using globals.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setRuntimeVar($name, $value)
    {
        $this->runtime_vars[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasRuntimeVar($name)
    {
        return array_key_exists($name, $this->runtime_vars);
    }

    /**
     * @param string $name
     */
    public function unsetRuntimeVar($name)
    {
        unset($this->runtime_vars[$name]);
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getRuntimeVar($name, $default = '__throw__')
    {
        if (!array_key_exists($name, $this->runtime_vars)) {
            if ($default === '__throw__') {
                return new \OutOfRangeException();
            }
            return $default;
        }

        return $this->runtime_vars[$name];
    }
}
