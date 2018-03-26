<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Service;

use Application\DeskPRO\App;
use DpSys\License;

class ErrorReporter
{
    private static function getBasicData($send_all_stats = false)
    {
        if (isset($GLOBALS['DP_DISABLE_SENDREPORTS'])) {
            return [];
        }

        if (!defined('DP_BUILD_NUM')) {
            return [];
        }

        $reduced_lic_reports = false;
        if (class_exists('Application\\DeskPRO\\App')) {
            try {
                if (App::getSetting('core.enable_reduced_lic_reports')) {
                    $reduced_lic_reports = true;
                }
            } catch (\Exception $e) {
            }
        }

        if ($send_all_stats) {
            $reduced_lic_reports = false;
        }

        if ($reduced_lic_reports) {
            $info = [
                'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            ];
        } else {
            if (class_exists('Application\\DeskPRO\\App')) {
                try {
                    $db = App::getDb();
                } catch (\Exception $e) {
                    $db = null;
                }
                $stats_fetcher = new \Application\InstallBundle\Data\ServerStats($db);
                $all_stats     = $stats_fetcher->getStats();
            } else {
                $all_stats = [];
            }

            $info = [
                'root'              => defined('DP_ROOT') ? DP_ROOT : '',
                'os'                => isset($all_stats['server_os']) ? $all_stats['server_os'] : '',
                'web_server'        => isset($all_stats['web_server']) ? $all_stats['web_server'] : '',
                'php_version'       => isset($all_stats['php_version']) ? $all_stats['php_version'] : '',
                'apc_version'       => isset($all_stats['apc_version']) ? $all_stats['apc_version'] : '',
                'mysql_version'     => isset($all_stats['mysql_version']) ? $all_stats['mysql_version'] : '',
                'server_ip'         => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '',
                'client_ip'         => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                'client_referrer'   => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'client_request'    => isset($_REQUEST) ? implode(', ', array_keys($_REQUEST)) : '',
            ];

            if ($send_all_stats && $all_stats) {
                $info = array_merge($info, $all_stats);
            }

            $info['hostname'] = @gethostname();
        }

        if (defined('DP_REQUEST_URL')) {
            $url = DP_REQUEST_URL;
        } elseif (defined('DP_INTERFACE')) {
            $url = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
            if (class_exists('Application\\DeskPRO\\App')) {
                try {
                    $url = App::getRequest()->getUri();
                } catch (\Exception $e) {
                }
            }
        } else {
            $url = '';
        }

        if (php_sapi_name() == 'cli') {
            $url = 'Command: '.implode(' ', $_SERVER['argv']);
        }

        $info['url'] = $url;

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if ($DP_ENV->isDebug()) {
            $info['DEV_MODE'] = 1;
        }

        $info['build']     = $DP_ENV->getAppName();
        $info['build_num'] = $DP_ENV->getAppName();

        $versionNameFile = $DP_ENV->getAppDir().'/sys/config/build-name.txt';
        if (file_exists($versionNameFile)) {
            $info['build_name'] = trim(file_get_contents($versionNameFile));
        } else {
            $info['build_name'] = $DP_ENV->getAppName();
        }

        if ((defined('DP_INTERFACE') && DP_INTERFACE != 'install') || (!isset($GLOBALS['DP_IS_INSTALL']) || !$GLOBALS['DP_IS_INSTALL'])) {
            try {
                $info['license_id'] = \DpSys\License::getLicense()->getLicenseId();
                $info['is_demo']    = \DpSys\License::getLicense()->isDemo();
            } catch (\Exception $e) {
                $info['license_id'] = '';
                $info['is_demo']    = false;
            }
        }

        return $info;
    }

    /**
     * Sends a heartbeat.
     */
    public static function sendHeartbeat()
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        if (isset($GLOBALS['DP_DISABLE_SENDREPORTS'])) {
            return '';
        }

        $data = self::getBasicData();

        if (!App::getSetting('core.enable_reduced_lic_reports')) {
            $database_stats = new \Application\DeskPRO\DBAL\DatabaseStats(App::getDb());
            $data           = array_merge($data, $database_stats->getStats());

            $data['setting_core_site_url']           = App::getSetting('core.site_url');
            $data['setting_core_install_time']       = App::getSetting('core.install_time');
            $data['setting_core_filestorage_method'] = App::getSetting('core.filestorage_method');
        }

        $data['setting_elastica_enabled'] = App::getSetting('elastica.enabled');
        $data['setting_core_deskpro_url'] = App::getContainer()->getBrandSetting('core.deskpro_url');
        $data['db_id_hash']               = md5($DP_ENV->getConfig('database.host').$DP_ENV->getConfig('database.dbnae').$DP_ENV->getConfig('database.user'));
        $data['license_code']             = App::getSetting('core.license');

        try {
            $client = new \Zend\Http\Client(null, ['timeout' => 20, 'strictredirects' => true, 'sslverifypeer' => false]);
            $client->setMethod(\Zend\Http\Request::METHOD_POST);
            $client->setUri(\DpSys\License::getSecureLicServer().'/api/heartbeat.json');
            $client->getRequest()->getPost()->fromArray($data);
            $r = $client->send();

            return $r->getBody();
        } catch (\Exception $e) {
            error_log(sprintf('sendHeartbeat %s %s', $e->getCode(), $e->getMessage()));

            return;
        }
    }

    /**
     * @static
     *
     * @param $person
     * @param $message
     */
    public static function sendSupportMessage($subject, $message, $name, $email_address)
    {
        $data = [
            'message' => $message,
            'name'    => $name,
            'email'   => $email_address,
            'url'     => App::getContainer()->getBrandSetting('core.deskpro_url'),
            'lic_id'  => License::getLicense()->getLicenseId(),
            'subject' => $subject,
        ];

        try {
            $client = new \Zend\Http\Client(null, ['timeout' => 5, 'strictredirects' => true, 'sslverifypeer' => false]);
            $client->setMethod(\Zend\Http\Request::METHOD_POST);
            $client->setUri(\DpSys\License::getSecureLicServer().'/api/data-submit/submit-feedback.json');
            $client->getRequest()->getPost()->fromArray($data);
            $client->setEncType('application/x-www-form-urlencoded; charset=UTF-8');
            $client->setAdapter('Zend\Http\Client\Adapter\Curl');
            $r = $client->send();

            return true;
        } catch (\Exception $e) {
            \DpSys\LowError\SystemErrorHandler::logException($e, true, 'failed_send_support_message');

            return false;
        }
    }
}
