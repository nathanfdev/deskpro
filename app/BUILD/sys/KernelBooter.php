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

namespace DeskPRO\Kernel;

require_once DP_ROOT.'/sys/DpShutdown.php';
require_once DP_ROOT.'/sys/Kernel/HelpdeskOfflineMessage.php';

use Application\DeskPRO\App;
use Application\DeskPRO\Console\CronApplication;
use DeskPRO\Bundle\AppBundle\Debug\HttpCacheDebugPrinter;
use DeskPRO\Bundle\PortalBundle\HttpCache\PortalHttpCache;
use DeskPRO\Component\Filesystem\SafeFile;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

class KernelBooter
{
    protected static $_cache_file = null;

    /**
     * Builds the main $DP_CONFIG array from config.php.
     *
     * @return mixed
     */
    public static function bootstrapConfig()
    {
        static $has_loaded = false;
        if ($has_loaded) {
            return;
        }

        $has_loaded = true;

        #------------------------------
        # Load main config now
        #------------------------------

        global $DP_CONFIG;
        dp_load_config();

        // Enable/disable display_errors based on enable_display_errors config (default is to hide)
        if (
            (isset($DP_CONFIG['enable_display_errors']) && $DP_CONFIG['enable_display_errors'])
            || (isset($GLOBALS['DP_enable_display_errors']) && $GLOBALS['DP_enable_display_errors'])
            || (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev'])
        ) {
            @ini_set('display_errors', '1');
        } else {
            @ini_set('display_errors', '0');
        }

        #------------------------------
        # Set global blacklist/whitelist for file op
        #------------------------------

        require DP_ROOT.'/src/DeskPRO/Component/Filesystem/SafeFile.php';
        SafeFile::setEmitWarningsOption(true);
        SafeFile::addBlacklistFile(DP_WEB_ROOT.'/config.php');
        SafeFile::addBlacklistDir(dp_get_backup_dir());
        SafeFile::addBlacklistDir(dp_get_debug_dir());
        SafeFile::addBlacklistDir(dp_get_blob_dir());
        SafeFile::addBlacklistDir(dp_get_cache_dir());
        SafeFile::addBlacklistDir(dp_get_tmp_dir());
        SafeFile::addBlacklistDir(dp_get_data_dir());
    }

    /**
     * Includes the libraries and autoloading required for the system to boot.
     *
     * @param $debug
     *
     * @return mixed
     */
    public static function bootstrapLib($debug)
    {
        static $has_loaded = false;
        if ($has_loaded) {
            return;
        }

        $has_loaded = true;

        if (($debug || defined('DP_BUILDING') || !file_exists(DP_ROOT.'/sys/bootstrap.php')) && !defined('DP_USE_COMPILED_BOOTSTRAP')) {
            require DP_ROOT.'/sys/bootstrap-dev.php';
        } else {
            require DP_ROOT.'/sys/bootstrap.php';
        }

        if (isset($GLOBALS['DP_AUTOLOADER']) && !dp_get_config('no_use_classmap_file') && file_exists(DP_ROOT.'/sys/cache/classmap.php')) {
            $map = require DP_ROOT.'/sys/cache/classmap.php';
            if ($map) {
                $GLOBALS['DP_AUTOLOADER']->registerClassNames($map);
            }
        }

        require DP_ROOT.'/sys/Kernel/compat.php';
        require DP_ROOT.'/sys/system.php';
    }

    /**
     * Gets the environment ready for execution.
     */
    public static function bootstrapEnv()
    {
        #------------------------------
        # Normalize env
        #------------------------------

        setlocale(LC_CTYPE, 'C');
        date_default_timezone_set('UTC');
        ini_set('default_charset', 'UTF-8');
        libxml_disable_entity_loader(true);

        \Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor-src/php-utf8');
    }

    /**
     * Boots a web kernel.
     *
     * @param null $request
     */
    public static function bootWeb($request = null)
    {
        global $DP_CONFIG;

        self::bootstrapConfig();

        $env   = 'prod';
        $debug = false;

        if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
            $env   = 'dev';
            $debug = true;
        }

        self::ensureEnvFiles($env);
        // defer lib loading until we know we're not serving a cache

        if ($request) {
            $path        = $request->getPathInfo();
            $base_path   = $request->getBasePath();
            $request_uri = $request->getRequestUri();
        } else {
            $path        = self::getPathInfo();
            $base_path   = self::getBasePath();
            $request_uri = self::getRequestUri();
        }

        $kernel = false;

        if (preg_match('#^/dp\-ping(/|\?|$)#', $path)) {
            header('Content-type: application/json');
            echo '{"deskpro": true, "interface": "user"}';
            exit;
        } elseif (preg_match('#^/(agent|admin|api|reports|billing)/dp-ping(/|\?|$)#', $path, $m)) {
            header('Content-type: application/json');
            echo '{"deskpro": true, "interface": "'.$m[1].'"}';
            exit;
        }

        $agent_env    = null;
        $kernel_class = 'DeskPRO\\Kernel\\DpKernel';
        if (preg_match('#^/agent(/|\?|$)#', $path)) {
            define('DP_INTERFACE', 'agent');
            define('OLD_AGENT', true);
            $agent_env = 'dev_old_agent';
        } elseif (preg_match('#^/new-agent(/|\?|$)#', $path)) {
            define('DP_INTERFACE', 'agent');
        } elseif (preg_match('#^/adm(in)?(/|\?|$)#', $path)) {
            define('DP_INTERFACE', 'admin');
        } elseif (preg_match('#^/logout/.+#', $path)) {
            define('DP_INTERFACE', 'agent');
        } elseif (preg_match('#^/billing(/|\?|$)#', $path)) {
            define('DP_INTERFACE', 'billing');
        } elseif (preg_match('#^/reports(/|\?|$)#', $path)) {
            define('DP_INTERFACE', 'reports');
        } elseif (preg_match('#^/api/v2(/|\?|$)#', $path)) {
            define('DP_INTERFACE', 'apiv2');

            self::bootstrapLib($debug);
            self::bootstrapEnv();
            require_once DP_ROOT.'/sys/Kernel/ApiKernel.php';
            $kernel = new ApiKernel($env, $debug);

            if ('dev' === $env) {
                Debug::enable();
            }

            $request  = Request::createFromGlobals();
            $response = $kernel->handle($request);
            $response->send();
            $kernel->terminate($request, $response);
            exit;
        } elseif (preg_match('#^/api(/|\?|$)#', $path)) {
            define('DP_INTERFACE', 'api');
        } elseif (preg_match('#^/install(/|\?|$)#', $path)) {
            if (dp_get_config('is_installed_flag')) {
                echo deskpro_install_basic_error('The database details in <var>config.php</var> are invalid or the database is not a valid DeskPRO database.<br/><br/>If this is a mistake and you intend to create a new installation into a new database, you must first delete the file <var>data/is_installed.dat</var> to make the installer function again.', 'Error');
                exit;
            }

            $kernel_class = 'DeskPRO\\Kernel\\InstallKernel';
            define('DP_INTERFACE', 'install');

            // Always force full URL with trailing slash
            if (strpos($request_uri, '/index.php/install/') === false) {
                header('Location: '.$base_path.'/index.php/install/');
                exit;
            }
        } elseif (preg_match('#^/tech(/|\?|$)#i', $path)) {
            header('Location: '.$base_path.'/agent');
            exit;
        } elseif (preg_match('#^/admincp(/|\?|$)#i', $path)) {
            header('Location: '.$base_path.'/admin');
            exit;
        } elseif (preg_match('#^/file.php/?(.*?)$#i', $path, $m)) {
            $url = $base_path.'/file.php/'.$m[1].(!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '');
            header('Location: '.$url);
            exit;
        } else {
            define('DP_INTERFACE', 'user');

            // exit early on asset 404s
            if (preg_match('#^/web/#', $path)) {
                header('HTTP/1.0 404 Not Found');
                echo 'File not found. (no asset)';
                exit;
            }

            try {
                self::bootstrapLib($debug);
                self::bootstrapEnv();
                require_once DP_ROOT.'/sys/Kernel/PortalKernel.php';
                $kernel = new PortalKernel($env, $debug);

                // add our reverse proxy
                $dp_cache_disabled = isset($GLOBALS['DP_DISABLE_CACHE']) && $GLOBALS['DP_DISABLE_CACHE'];
                if (!$dp_cache_disabled && !preg_match('#^/portal/api(/|\?|$)#i', $path)) {
                    require_once DP_ROOT.'/src/DeskPRO/Bundle/PortalBundle/HttpCache/PortalHttpCache.php';
                    $kernel = new PortalHttpCache($kernel, dp_get_data_dir().'/http_cache/portal');
                }

                if ('dev' === $env) {
                    Debug::enable();
                }

                $request  = Request::createFromGlobals();
                $response = $kernel->handle($request);

                // ------------------------------------------------------------------------------------
                // debug http cache
                //
                if (array_key_exists('dev', $DP_CONFIG['debug']) && $DP_CONFIG['debug']['dev']) {
                    if ($kernel instanceof PortalHttpCache
                        && strpos($request->getPathInfo(), '/_wdt') === false
                        && strpos($request->getPathInfo(), '/_profile') === false
                        && !$request->isXmlHttpRequest()
                        && strpos($response->headers->get('Content-Type'), 'application/json') === false
                        && strpos($response->headers->get('Content-Type'), 'text/javascript') === false
                    ) {
                        $pretty_log       = HttpCacheDebugPrinter::debugPortalCacheKernel($kernel);
                        $response_content = $response->getContent();
                        $final_content    = $response_content.$pretty_log;
                        $response->setContent($final_content);
                        $response->headers->set('Content-Length', strlen($final_content));
                    }

                    // sometimes the web debug toolbar crashes due to a fully cached page not having
                    // a new profile code, so lets just disable those annoying JS popups here in dev mode
                    if (strpos($request->getPathInfo(), '/_wdt') !== false && $response->getStatusCode() != 200) {
                        exit;
                    }
                }
                //
                //
                // ------------------------------------------------------------------------------------

                $response->send();

                $kernel->terminate($request, $response);
            } catch (DBALException $e) {
                // note: this try catch block is directly copied from old portal code in this booter, but we added code=0
                if ($e->getCode() == '0' || $e->getCode() == '2002' || $e->getCode() == '1049' || $e->getCode() == '1044' || $e->getCode() == '1045') {
                    // This will show an error page if already installed, so the redirect to install wont happen
                    deskpro_handle_boot_db_exception($e);

                    header('Location: '.$request->getBasePath().'/index.php/install/');
                    exit;
                }
                KernelErrorHandler::logException($e);
            } catch (\Exception $e) {
                echo deskpro_install_basic_error('There was an error while trying to serve your request.<br/><br/>If you are an administrator, you should check <var>data/logs/error.log</var>', 'Error');
                KernelErrorHandler::logException($e);
            }

            exit;
            //
            // end boot and run portal
            //
        }

        // No access to install or dev from cloud
        if (defined('DPC_IS_CLOUD') && (DP_INTERFACE == 'dev' || DP_INTERFACE == 'install')) {
            exit;
        }

        #------------------------------
        # Handle request
        #------------------------------

        if (!$kernel) {
            self::bootstrapLib($debug);
            self::bootstrapEnv();
        }

        if (!$request) {
            $request = \Application\DeskPRO\HttpFoundation\Request::createfromGlobals();
        }

        dp_pagelog_reset();
        dp_pagelog_set('user_agent', !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
        dp_pagelog_set('user_ip', dp_get_user_ip_address());
        dp_pagelog_set('request_id', defined('DP_REQUEST_ID') ? DP_REQUEST_ID : null);
        dp_pagelog_set('page_url', $request->getRequestUri());

        $trust_option = isset($GLOBALS['DP_CONFIG']['trust_proxy_data']) && $GLOBALS['DP_CONFIG']['trust_proxy_data'] ? $GLOBALS['DP_CONFIG']['trust_proxy_data'] : null;
        $trust_list   = array();
        if ($trust_option) {
            if (!is_array($trust_option)) {
                $trust_option = array($trust_option);
            }
            foreach ($trust_option as $opt) {
                if (is_string($opt) && $opt[0] == '@') {
                    $file = substr($opt, 1);
                    // A relative file starts with ~
                    if ($file[0] == '~') {
                        $file = DP_ROOT.substr($file, 1);
                    }

                    if (file_exists($file)) {
                        $inc_opts = @include $file;
                        if ($inc_opts) {
                            $trust_list = array_merge($trust_list, $inc_opts);
                        }
                    }
                } else {
                    $trust_list[] = $opt;
                }
            }
        }

        if ($trust_list) {
            \Application\DeskPRO\HttpFoundation\Request::setTrustedProxies($trust_list);
            \Symfony\Component\HttpFoundation\Request::setTrustedProxies($trust_list);
        }

        $GLOBALS['DP_MAIN_REQUEST'] = $request;

        try {
            define('DP_REQUEST_URL', $request->getUri());
        } catch (\UnexpectedValueException $e) {
            // thrown when there is a bad hostname provided
            header('HTTP/1.1 400 Bad request', true, 401);
            echo $e->getMessage();
            exit;
        }

        try {
            if (!$kernel) {
                if ($kernel == 'DeskPRO\\Kernel\\InstallKernel') {
                    $kernel = new $kernel_class($agent_env ?: $env, $debug);
                } else {
                    $kernel = new $kernel_class($agent_env ?: $env, $debug, DP_INTERFACE);
                }
            }
            $response = $kernel->handle($request);

            if (defined('DP_REQUEST_ID')) {
                $response->headers->set('X-DeskPRO-RequestID', DP_REQUEST_ID);
            }

            $response->send();

            dp_pagelog_set('response_type', $response->headers->get('Content-Type'));
            dp_pagelog_set('response_code', $response->getStatusCode());
            dp_pagelog_set('response_size', strlen($response->getContent()));
        } catch (\Exception $e) {
            if ($e instanceof DBALException || $e instanceof \PDOException) {
                if ($e->getCode() == '2002' || $e->getCode() == '1049' || $e->getCode() == '1044' || $e->getCode() == '1045') {
                    // This will show an error page if already installed, so the redirect to install wont happen
                    deskpro_handle_boot_db_exception($e);

                    header('Location: '.$request->getBasePath().'/index.php/install/');
                    exit;
                }
                throw $e;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Boots the CLI.
     *
     * @param string $env
     * @param bool   $debug
     */
    public static function bootCli($env = 'prod', $debug = false, $other_kernel = null)
    {
        try {
            if (!$other_kernel) {
                static::ensureCli();
                $app = static::getCliApp('cmd', $env, $debug);

                if (!$app) {
                    return;
                }

                $GLOBALS['DP_IS_IN_CLI'] = true;
                $app->setAutoExit(false);

                libxml_disable_entity_loader(false);
                $return = $app->run(new ArgvInput());
                unset($GLOBALS['DP_IS_IN_CLI']);

                return $return;
            } else {
                if ($other_kernel == 'portal') {
                    self::bootstrapConfig();

                    if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
                        $env   = 'dev';
                        $debug = true;
                    }

                    self::bootstrapLib($debug);
                    self::bootstrapEnv();

                    $input = new ArgvInput();
                    $env   = $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'dev');
                    $debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(
                            array('--no-debug', '')
                        ) && $env !== 'prod';

                    require_once DP_ROOT.'/sys/Kernel/PortalKernel.php';
                    $kernel = new PortalKernel($env, $debug);
                    $app    = new Application($kernel);

                    libxml_disable_entity_loader(false); // needed on some machines

                    return $app->run($input);
                }

                if ($other_kernel == 'api') {
                    self::bootstrapConfig();

                    if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
                        $env   = 'dev';
                        $debug = true;
                    }

                    self::bootstrapLib($debug);
                    self::bootstrapEnv();

                    $input = new ArgvInput();
                    $env   = $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'dev');
                    $debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(
                            array('--no-debug', '')
                        ) && $env !== 'prod';

                    require_once DP_ROOT.'/sys/Kernel/ApiKernel.php';
                    $kernel = new ApiKernel($env, $debug);

                    $app = new Application($kernel);

                    libxml_disable_entity_loader(false); // needed on some machines

                    return $app->run($input);
                }
            }
        } catch (\Exception $e) {
            KernelErrorHandler::handleException($e);
        }
    }

    /**
     * Boots the CLI and runs the cron command.
     *
     * @param string $env
     * @param bool   $debug
     */
    public static function bootCron($env = 'prod', $debug = false)
    {
        static::ensureCli();
        $app = static::getCliApp('cron', $env, $debug, true);

        if (!$app) {
            return;
        }

        if (isset($GLOBALS['DP_USING_TESTING_CONFIG'])) {
            echo "(Tests are running)\n";

            return;
        }

        $lock_file = dp_get_tmp_dir().'/cron.lock';
        $lock_fp   = null;

        // Use a file lock for better "cron is still running" detection
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if (!in_array('-f', $_SERVER['argv']) && !in_array('--force', $_SERVER['argv'])) {
                $skip_lock_err = false;
                if (file_exists($lock_file)) {
                    $t = (int) file_get_contents($lock_file);
                    if ($t < time() - 900) {
                        $skip_lock_err = true;
                        @unlink($lock_file);
                    }
                }

                $lock_fp = @fopen($lock_file, 'c');
                @chmod($lock_file, 0777);
                if ($lock_fp) {
                    if (!@flock($lock_fp, \LOCK_EX | \LOCK_NB)) {
                        if (in_array('--verbose', $_SERVER['argv']) || in_array('-v', $_SERVER['argv'])) {
                            echo "Lock file still locked, cron already running: $lock_file\n";
                        }
                        if (!$skip_lock_err) {
                            exit;
                        }
                    }

                    @ftruncate($lock_fp, 0);
                    @fwrite($lock_fp, time());
                }
            }
        }

        $check_twitter = false;
        $check_indexer = false;

        $do_upgrade = false;
        try {
            if (\Application\DeskPRO\App::getSetting('core.upgrade_time') && \Application\DeskPRO\App::getSetting('core.upgrade_time') <= time()) {
                $do_upgrade = true;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        $argv = $_SERVER['argv'];
        array_shift($argv); // remove cron.php

        if ($do_upgrade) {
            $argv = array();
            array_unshift($argv, 'cron.php', 'dp:internal-upgrade-runner');
        } else {
            $do_collation_change = false;
            try {
                if (\Application\DeskPRO\App::getSetting('core.db_collation_change')) {
                    $do_collation_change = \Application\DeskPRO\App::getSetting('core.db_collation_change');
                }
            } catch (\Exception $e) {
                throw $e;
            }

            if ($do_collation_change) {
                \Application\DeskPRO\App::getDb()->executeQuery("
                    DELETE FROM settings WHERE name = 'core.db_collation_change'
                ");
                array_unshift($argv, 'cron.php', 'dp:db-collation-change', "--collation=$do_collation_change");
            } else {
                array_unshift($argv, 'cron.php', 'dp:worker-job'); // so we can add the command name in the right spot
            }

            $check_twitter = true;
            $check_indexer = true;
            self::checkImporter();
        }

        if ($check_twitter && !defined('DPC_IS_CLOUD') && \Application\DeskPRO\App::getConfig('enable_twitter')) {
            $twitter_ping = \Application\DeskPRO\App::getSetting('core.twitter_ping');
            if (!$twitter_ping || $twitter_ping < time() - 60) {
                if (\Application\DeskPRO\App::getDb()->fetchColumn('SELECT COUNT(*) FROM twitter_accounts')) {
                    if (file_exists(dp_get_data_dir().'/twitter.pid')) {
                        $twitter_pid = intval(file_get_contents(dp_get_data_dir().'/twitter.pid'));
                    } else {
                        $twitter_pid = null;
                    }

                    if ($twitter_pid !== 0) {
                        // need to restart the twitter runner in the background

                        $file     = escapeshellarg(DP_ROOT.'/bin/twitter.php');
                        $php_path = dp_get_php_path(false);

                        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                            // this is needed as we need a fake window to hide the process
                            $php_path = str_replace('php-win.exe', 'php.exe', $php_path);
                            $file     = str_replace('/', '\\', $file);

                            if (class_exists('\COM', false)) {
                                $shell = new \COM('WScript.Shell');
                                $shell->Run("$php_path $file", 0, false);
                            } else {
                                pclose(popen("start \"dptwitter\" /MIN $php_path $file", 'r'));
                            }
                        } else {
                            exec("nohup $php_path $file > /dev/null 2> /dev/null &");
                        }
                    }
                }
            }
        }

        if ($check_indexer && !defined('DPC_IS_CLOUD')) {
            @set_time_limit(0);
            $index_reset = \Application\DeskPRO\App::getSetting('elastica.requires_reset');
            if ($index_reset) {
                try {
                    $id = mt_rand(10000, 99999);
                    \Application\DeskPRO\App::getDb()->insertIgnore('settings', array('name' => 'elastica.requires_reset_started', 'value' => $id));

                    $file     = escapeshellarg(realpath(DP_ROOT.'/../cmd.php'));
                    $args     = 'dp:elastica:populate --auto-reset '.$id;
                    $php_path = dp_get_php_path(false);

                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        // this is needed as we need a fake window to hide the process
                        $php_path = str_replace('php-win.exe', 'php.exe', $php_path);
                        $file     = str_replace('/', '\\', $file);

                        if (class_exists('\COM', false)) {
                            $shell = new \COM('WScript.Shell');
                            $shell->Run("$php_path $file", 0, false);
                        } else {
                            pclose(popen("start \"dpindexer\" /MIN $php_path $file $args", 'r'));
                        }
                    } else {
                        exec("nohup $php_path $file $args > /dev/null 2> /dev/null &");
                    }
                } catch (\Exception $e) {
                }
            }
        }

        $input = new \Symfony\Component\Console\Input\ArgvInput($argv);

        $GLOBALS['DP_IS_IN_CLI'] = true;
        $app->setAutoExit(false);
        $return                  = $app->run($input);
        $GLOBALS['DP_IS_IN_CLI'] = false;

        if ($lock_fp) {
            @flock($lock_fp, \LOCK_UN);
            @fclose($lock_fp);
            @unlink($lock_file);
        }

        return $return;
    }

    protected static function checkImporter()
    {
        $trigger = dp_get_data_dir().'/importer_cron.pid';
        $pid     = @file_get_contents($trigger);
        if (false === $pid || (int) $pid) {
            if (defined('DPC_SITE_ID')) {
                try {
                    // TODO should set a status indicator when starting a job in admin, instead of this ugly where
                    if (\Application\DeskPRO\App::getDb()->fetchColumn("
                        SELECT COUNT(*)
                        FROM datastore
                        WHERE name LIKE 'importers.%' AND data LIKE ?
                        LIMIT 1
                    ", array('%"status";s:7:"pending"%'))) {
                        file_put_contents(dp_get_data_dir().'/importer_cron.pid', 0);

                        return self::checkImporter();
                    }
                } catch (\Exception $e) {
                }
            }

            return;
        }
        file_put_contents($trigger, getmypid());
        register_shutdown_function(function () use ($trigger) {
            unlink($trigger);
        });

        $dpc = '';
        if (defined('DPC_SITE_ID')) {
            $dpc = ' --dpc-site-id '.DPC_SITE_ID;
        }
        $command  = escapeshellcmd(DP_ROOT.'/../cmd.php'.$dpc.' dp:import:run --config-from-db');
        $php_path = dp_get_php_path(false);

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            return exec("$php_path $command > /dev/null 2>&1 &");
        }

        // this is needed as we need a fake window to hide the process
        $php_path = str_replace('php-win.exe', 'php.exe', $php_path);
        $command  = str_replace('/', '\\', $command);

        if (!class_exists('\COM', false)) {
            return pclose(popen("start \"dpimport\" /MIN $php_path $command", 'r'));
        }

        $shell = new \COM('WScript.Shell');
        $shell->Run("$php_path $command", 0, false);
    }

    /**
     * Boot tests.
     */
    public static function bootTests()
    {
        define('DP_INTERFACE', 'cli');
        $GLOBALS['DP_IS_IN_CLI'] = true;
        self::bootstrapConfig();
        self::bootstrapLib(true);
        self::bootstrapEnv();
    }

    /**
     * Boots the CLI runs the import CLI command.
     *
     * @param string $env
     * @param bool   $debug
     */
    public static function bootImport($env = 'prod', $debug = false)
    {
        static::ensureCli();

        echo 'The version of DeskPRO you have downloaded does not handle importing.';

        echo "\n\nRefer to the DeskPRO knowledgebase for a link to the correct version:\n";
        echo 'http://support.deskpro.com/kb/articles/116-upgrading-to-deskpro-v4';

        echo "\n\nYou should download the DeskPRO version mentioned in the above article\n";
        echo "and then try running this command again.\n";
        exit(1);

        $app = static::getCliApp('import', $env, $debug);

        $argv = $_SERVER['argv'];
        array_shift($argv); // remove cron.php
        array_unshift($argv, 'import.php', 'dp:import', '--run'); // so we can add the command name in the right spot
        $input = new \Symfony\Component\Console\Input\ArgvInput($argv);

        $GLOBALS['DP_IS_IN_CLI']  = true;
        $GLOBALS['DP_IS_INSTALL'] = true;
        $app->run($input);
        $GLOBALS['DP_IS_IN_CLI']  = false;
        $GLOBALS['DP_IS_INSTALL'] = false;
    }

    /**
     * Boots the CLI runs the upgrade CLI command.
     *
     * @param string $env
     * @param bool   $debug
     */
    public static function bootUpgrade($env = 'prod', $debug = false)
    {
        static::ensureCli();

        $app = static::getCliApp('upgrade', $env, $debug);

        $argv = $_SERVER['argv'];
        array_shift($argv); // remove upgrade.php
        array_unshift($argv, 'upgrade.php', 'dp:upgrade'); // so we can add the command name in the right spot
        $input = new \Symfony\Component\Console\Input\ArgvInput($argv);

        $GLOBALS['DP_IS_IN_CLI']  = true;
        $GLOBALS['DP_IS_INSTALL'] = true;
        $app->run($input);
        $GLOBALS['DP_IS_IN_CLI']  = false;
        $GLOBALS['DP_IS_INSTALL'] = false;
    }

    /**
     * Creates a CLI kernel, and create an console app.
     *
     * @param string $env
     * @param bool   $debug
     *
     * @return \Symfony\Bundle\FrameworkBundle\Console\Application
     */
    public static function getCliApp($mode, $env = 'prod', $debug = false, $enforce_offline_mode = false)
    {
        global $DP_CONFIG;

        self::bootstrapConfig();

        if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
            $env   = 'dev';
            $debug = true;
        }

        self::ensureEnvFiles($env);
        self::bootstrapLib($debug);
        self::bootstrapEnv();

        if (defined('DP_BUILDING')) {
            $debug = false;
        }

        if (!defined('DP_INTERFACE')) {
            define('DP_INTERFACE', 'cli');
        }
        $kernel = new \DeskPRO\Kernel\DpKernel($env, $debug, DP_INTERFACE);
        $kernel->boot($mode);
/*
        try {
            if ($mode == 'cron' && $kernel->isUpgradePending()) {
                if (in_array('--verbose', $_SERVER['argv'])) {
                    echo "Upgrade pending\n";
                }

                return;
            }
            if ($enforce_offline_mode || ($mode == 'cron' && !\Application\DeskPRO\App::getSetting('core.setup_initial'))) {
                if ($kernel->isHelpdeskOffline()) {
                    if (in_array('--verbose', $_SERVER['argv'])) {
                        echo "Helpdesk offline\n";
                    }

                    return;
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            global $DP_CONFIG;
            if ($e->getCode() == '42S02' || @$DP_CONFIG['db']['user'] == 'YOUR_DATABASE_USER' || @$DP_CONFIG['db']['password'] == 'YOUR_DATABASE_PASS' || @$DP_CONFIG['db']['dbname'] == 'YOUR_DATABASE_NAME') {
                echo "DeskPRO is not yet installed. If you believe this a mistake, check your config.php\n";
                echo "file and ensure the database connection details are correct.\n";
                echo "\n";
                echo "The connection attempt resulted in the following error:\n[{$e->getCode()}] {$e->getMessage()}";
                echo "\n";
                exit;
            }

            // Otherwise unknown error we'll throw up
            throw $e;
        }*/

        if ($mode == 'cron') {
            $app = new CronApplication($kernel);
        } else {
            $app = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        }
        $app->setCatchExceptions(false);

        return $app;
    }

    /**
     * Ensures the current invocation is via the command-line.
     */
    public static function ensureCli()
    {
        if (php_sapi_name() != 'cli') {
            echo "This script must only be run from the CLI.\n";
            echo "Contact support@deskpro.com if you require assistance.\n";
            exit(1);
        }
    }

    /**
     * If in prod mode, ensures that the build files etc exist.
     * If not in prod mode, ensures that the cached ir exists and is writable.
     */
    public static function ensureEnvFiles($env)
    {
        global $DP_CONFIG;
        $cache_dir = DP_ROOT.'/sys/cache';
        $web_dir   = realpath(DP_ROOT.'/../web');

        $offline = false;
        if (is_file(dp_get_data_dir().'/helpdesk-offline.trigger')) {
            $offline = true;
        }

        #------------------------------
        # Prod mode: make sure built
        #------------------------------

        if ($env == 'prod' && (!is_dir($cache_dir.'/prod'))) {
            if (php_sapi_name() == 'cli') {
                if ($offline) {
                    echo HelpdeskOfflineMessage::getOfflineMessage();
                    exit(1);
                }
                echo <<<'TXT'
DeskPRO's internal build files are missing. If you are using a pristine copy of the source code, you will need to do one of the following:

    1) Enable dev mode by editing /config.php and adding these lines:

        $DP_CONFIG['debug'] = array();
        $DP_CONFIG['debug']['dev'] = true;
        $DP_CONFIG['debug']['raw_assets'] = array('all');

    2) Or alternatively you can build DeskPRO by running app/bin/build/build.php from the command-line.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

                exit(1);
            } else {
                if ($offline) {
                    echo HelpdeskOfflineMessage::getOfflinePage();
                    exit(1);
                }
                $html = <<<'HTML'
<p>DeskPRO's internal build files are missing. If you are using a pristine copy of the source code, you will need to do one of the following:<br /><br /></p>

<p>1) Enable dev mode by editing <code>/config.php</code> and adding these lines:

<pre>
$DP_CONFIG['debug'] = array();
$DP_CONFIG['debug']['dev'] = true;
$DP_CONFIG['debug']['raw_assets'] = array('all');
</pre>
</p>

<p>2) Or alternatively you can build DeskPRO by running <code>app/bin/build/build.php</code> from the command-line.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

                echo deskpro_install_basic_error($html, 'DeskPRO');
            }
            exit;

        #------------------------------
        # Dev mode, make sure cache dir writable
        #------------------------------
        } elseif ($env == 'dev' && (!is_dir($cache_dir) || !is_writable($cache_dir))) {
            if ($offline) {
                echo HelpdeskOfflineMessage::getOfflineMessage();
                exit(1);
            }
            if (php_sapi_name() == 'cli') {
                echo <<<TXT
DeskPRO is currently in dev mode which requires the cache directory at $cache_dir to be writable. Please
ensure this directory is writable and try again.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

                exit(1);
            } else {
                if ($offline) {
                    echo HelpdeskOfflineMessage::getOfflinePage();
                    exit(1);
                }
                $html = <<<HTML
<p>DeskPRO is currently in dev mode which requires the cache directory at <code>$cache_dir</code> to be writable. Please
make this directory writable and try again.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

                echo deskpro_install_basic_error($html, 'DeskPRO Dev Mode');
            }

            exit;

        #------------------------------
        # Dev mode, not using raw assets, no build files
        #------------------------------
        } elseif ($env == 'dev' && (empty($DP_CONFIG['debug']['raw_assets']) && !is_file($web_dir.'/build/js/agent-all.js'))) {
            if (php_sapi_name() == 'cli') {
                if ($offline) {
                    echo HelpdeskOfflineMessage::getOfflineMessage();
                    exit(1);
                }
                echo <<<'TXT'
You are running in dev mode but you have not enabled raw assets and assets have not been built yet. For pages to display properly, you will need to do one of the following:

    1) Enable raw assets by editing /config.php and adding this line:

        $DP_CONFIG['debug']['raw_assets'] = array('all');

    2) Or alternatively you can build assets by running app/bin/build/build-assetic.php from the command-line.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

                exit(1);
            } else {
                if ($offline) {
                    echo HelpdeskOfflineMessage::getOfflinePage();
                    exit(1);
                }
                $html = <<<'HTML'
<p>You are running in dev mode but you have not enabled raw assets and assets have not been built yet. For pages to display properly, you will need to do one of the following:<br /><br /></p>

<p>1) Enable raw assets by editing <code>/config.php</code> and adding this line:

<pre>
$DP_CONFIG['debug']['raw_assets'] = array('all');
</pre>
</p>

<p>2) Or alternatively you can build assets by running <code>app/bin/build/build-assetic.php</code> from the command-line.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

                echo deskpro_install_basic_error($html, 'DeskPRO Dev Mode');
            }
            exit;
        }
    }

    ####################################################################################################################
    # Request Helpers
    ####################################################################################################################

    /**
     * @var string
     */
    protected static $base_url;

    /**
     * @var string
     */
    protected static $base_path;

    /**
     * @var string
     */
    protected static $path_info;

    /**
     * @var string
     */
    protected static $request_uri;

    /**
     * @var string
     */
    protected static $languages;

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function getPathInfo()
    {
        if (self::$path_info !== null) {
            return self::$path_info;
        }

        $baseUrl = self::getBaseUrl();

        if (null === ($requestUri = self::getRequestUri())) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $baseUrl) && (false === ($pathInfo = substr(urldecode($requestUri), strlen(urldecode($baseUrl)))))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }

        self::$path_info = (string) $pathInfo;

        return self::$path_info;
    }

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function getBaseUrl()
    {
        if (self::$base_url !== null) {
            return self::$base_url;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $strpos = 'stripos';
            $filter = 'strtolower';
        } else {
            $strpos = 'strpos';
            $filter = function ($in) { return $in; };
        }

        $filename = $filter(basename((isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : null)));

        if ($filter(basename((isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null))) === $filename) {
            $baseUrl = (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null);
        } elseif ($filter(basename((isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null))) === $filename) {
            $baseUrl = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null);
        } elseif ($filter(basename((isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : null))) === $filename) {
            $baseUrl = (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : null); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
            $file    = (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '');
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = $strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = self::getRequestUri();

        if ($baseUrl && 0 === strpos($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $baseUrl;
        }

        if ($baseUrl && 0 === $strpos($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim(dirname($baseUrl), '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        self::$base_url = rtrim($baseUrl, '/');

        return self::$base_url;
    }

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function getBasePath()
    {
        if (self::$base_path !== null) {
            return self::$base_path;
        }

        $filename = isset($_SERVER['SCRIPT_FILENAME']) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
        $baseUrl  = self::getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

        if (basename($baseUrl) === $filename) {
            $basePath = dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath);
        }

        return rtrim($basePath, '/');
    }

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function getRequestUri()
    {
        if (self::$request_uri !== null) {
            return self::$request_uri;
        }

        $requestUri = '';

        if ((isset($_SERVER['X_REWRITE_URL']) ? $_SERVER['X_REWRITE_URL'] : null) && false !== stripos(PHP_OS, 'WIN')) {
            // check this first so IIS will catch
            $requestUri = (isset($_SERVER['X_REWRITE_URL']) ? $_SERVER['X_REWRITE_URL'] : null);
        } elseif ((isset($_SERVER['IIS_WasUrlRewritten']) ? $_SERVER['IIS_WasUrlRewritten'] : null) == '1' && (isset($_SERVER['UNENCODED_URL']) ? $_SERVER['UNENCODED_URL'] : null) != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = (isset($_SERVER['UNENCODED_URL']) ? $_SERVER['UNENCODED_URL'] : null);
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null);
            // HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
            $schemeAndHttpHost = self::getScheme().'://'.self::getHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            // IIS 5.0, PHP as CGI
            $requestUri = (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : null);
            if ((isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null)) {
                $requestUri .= '?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null);
            }
        }

        self::$request_uri = $requestUri;

        return self::$request_uri;
    }

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function getScheme()
    {
        return self::isSecure() ? 'https' : 'http';
    }

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function isSecure()
    {
        return
            (strtolower((isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null)) == 'on' || (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null) == 1)
            ||
            ((isset($_SERVER['SSL_HTTPS']) ? $_SERVER['SSL_HTTPS'] : null) == 1)
        ;
    }

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function getHttpHost()
    {
        $scheme = self::getScheme();
        $port   = self::getPort();

        if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
            return self::getHost();
        }

        return self::getHost().':'.$port;
    }

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function getPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
    }

    /**
     * @see \Symfony\Component\HttpFoundation\Request
     */
    public static function getHost()
    {
        if (!$host = (isset($_SERVER['HOST']) ? $_SERVER['HOST'] : null)) {
            if (!$host = (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null)) {
                $host = (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '');
            }
        }

        // Remove port number from host
        $host = preg_replace('/:\d+$/', '', $host);

        return trim($host);
    }

    /**#@+
     * Handling of shutdown stack and xdebug traces
     */
    private static function DeskPRO_Done_MarkerCheck()
    {
    }
    public static function DeskPRO_Done()
    {
        static $called = false;
        if ($called) {
            return;
        }
        $called = true;

        \DpShutdown::run();
    }
}
