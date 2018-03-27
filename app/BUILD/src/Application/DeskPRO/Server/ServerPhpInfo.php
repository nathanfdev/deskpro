<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Server;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Orb\Util\Env;

class ServerPhpInfo
{
    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var string
     */
    private $urlAuth;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(AppEnvInterface $appEnv, $baseUrl, $urlAuth)
    {
        $this->appEnv  = $appEnv;
        $this->baseUrl = $baseUrl;
        $this->urlAuth = $urlAuth;
    }

    /**
     * @param bool $noencode
     *
     * @return array
     */
    public function getPhpInfo($noencode = false)
    {
        return $this->_getInfo($noencode);
    }

    /**
     * @param bool $noencode
     *
     * @return array
     */
    protected function _getInfo($noencode = false)
    {
        //------------------------------
        // Binary paths
        //------------------------------

        $binary_paths = [
            'php'       => $this->appEnv->getConfig('paths.php_path'),
            'mysql'     => $this->appEnv->getConfig('paths.mysql_path'),
            'mysqldump' => $this->appEnv->getConfig('paths.mysqldump_path'),
        ];

        //------------------------------
        // Web PHP
        //------------------------------

        $web_php               = [];
        $web_php['php_config'] = [
            'version'      => phpversion(),
            'memory_limit' => Env::getMemoryLimit(),
            'error_log'    => ini_get('error_log'),
        ];

        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        preg_match('#<body.*?>(.*?)</body>#ms', $phpinfo, $m);

        if (isset($m[1])) {
            $phpinfo = $m[1];
        }

        $web_php['phpinfo']              = $phpinfo;
        $web_php['ini_path']             = Env::getPhpIniPathFromInfo($web_php['phpinfo']);
        $web_php['effective_max_upload'] = Env::getEffectiveMaxUploadSize();

        //------------------------------
        // CLI PHP
        //------------------------------

        $cli_php = ['phpinfo' => null, 'php_config' => null];

        if (file_exists($this->appEnv->getUserCacheDir().'/cli-phpinfo.html')) {
            $phpinfo             = file_get_contents($this->appEnv->getUserCacheDir().'/cli-phpinfo.html');
            $cli_php['ini_path'] = Env::getPhpIniPathFromInfo($phpinfo);

            if (strpos($phpinfo, '<body') === false) {
                if (!$noencode) {
                    $phpinfo = '<code>'.nl2br(htmlspecialchars($phpinfo)).'</code>';
                }
            } else {
                preg_match('#<body.*?>(.*?)</body>#ms', $phpinfo, $m);

                if (isset($m[1])) {
                    $phpinfo = $m[1];
                }
            }

            $cli_php['phpinfo'] = $phpinfo;
        }

        if (file_exists($this->appEnv->getUserCacheDir().'/cli-phpconfig.json')) {
            $cli_php['php_config'] = @json_decode(file_get_contents($this->appEnv->getUserCacheDir().'/cli-phpconfig.json'), true);
        }

        $has_apc = false;

        if (function_exists('apc_store') && ini_get('apc.enabled')) {
            $has_apc = true;
        }

        $has_wincache = false;

        if (extension_loaded('wincache') && ini_get('wincache.ocenabled')) {
            $has_wincache = true;
        }

        $debug_settings = [];

        $auth      = $this->urlAuth;
        $baseUrl   = $this->baseUrl;
        $make_link = function ($act, $vars = []) use ($baseUrl, $auth) {
            $vars['auth'] = $auth;

            return $this->baseUrl.'/__serverinfo/'.$act.'?'.http_build_query($vars);
        };

        $web_php_link = $make_link('phpinfo');
        $cli_php_link = $make_link('phpinfo-cli');

        $opcache_link = null;
        if (version_compare(phpversion(), '5.5.0', '>=') && extension_loaded('Zend OPcache') && (int) ini_get('opcache.enable')) {
            $opcache_link = $make_link('opcache');
        }

        return [
            'binary_paths'   => $binary_paths,
            'web_php'        => $web_php,
            'cli_php'        => $cli_php,
            'has_apc'        => $has_apc,
            'has_wincache'   => $has_wincache,
            'debug_settings' => $debug_settings,
            'web_php_link'   => $web_php_link,
            'cli_php_link'   => $cli_php_link,
            'opcache_link'   => $opcache_link,
        ];
    }
}
