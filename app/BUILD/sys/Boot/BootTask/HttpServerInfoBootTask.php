<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpSys\Boot\BootTask;

use Symfony\Component\HttpFoundation\Request;

/**
 * Handles /__serverinfo/ URLs.
 *
 * Note that this does not return any meaningful resources because this task
 * will output directly to the browser and then exit;
 */
class HttpServerInfoBootTask implements BootTaskInterface
{
    /**
     * @var \DpRun\DpEnv
     */
    private $env;

    /**
     * @var array
     */
    private $params;

    public function run(\DpRun\DpEnv $env, array $resources)
    {
        $this->env    = $env;
        $this->params = $resources['serverinfo_params'];

        $action = $resources['serverinfo_action'];
        $auth   = @$_GET['auth'] ?: @$resources['serverinfo_params']['auth'];

        // Will exit if any match
        $this->authlessServerChecks($action);

        if (!$this->checkAuth($auth)) {
            echo "The auth code in the URL you are trying to view is invalid. Please run the dp:web-server-info command to generate new links.\n";
            exit;
        }

        // Will exit if any match
        $this->authRequiredServerChecks($action);

        echo 'Unknown serverinfo request.';
        exit;
    }

    private function authlessServerChecks($action)
    {
        switch ($action) {
            case 'ping':
                $msg = $this->env->getDatManager()->readTxtFile('pong_message', null);

                if (isset($_GET['jsonp']) && isset($_GET['callback'])) {
                    header('Content-Type: application/javascript');
                    $ret = ['response' => 'pong'];
                    if ($msg) {
                        $ret['message'] = $msg;
                    }
                    echo $_GET['callback'];
                    echo '(';
                    echo json_encode($ret);
                    echo ');';
                } else {
                    header('Content-Type: text/plain');
                    echo 'pong';
                    if ($msg) {
                        echo "\n";
                        echo $msg;
                    }
                }
                exit;

            case 'check_http_methods':
                header('Content-Type: text/plain');
                echo 'HTTP_METHOD_'.strtoupper(@$_SERVER['REQUEST_METHOD']);
                exit;
        }
    }

    private function authRequiredServerChecks($action)
    {
        $logsPath = $this->env->getUserLogsDir();

        switch ($action) {
            case 'phpinfo':
                phpinfo();
                exit;

            case 'phpinfo-cli':
                $file = $this->env->getUserCacheDir().'/cli-phpinfo.html';
                if (!file_exists($file)) {
                    echo "PHP Info for the CLI has not been initialized yet. Please run the dp:web-server-info command.\n";
                    exit;
                }

                $fileContent = file_get_contents($file);

                if (strpos($fileContent, '<!DOCTYPE') !== false) {
                    header('Content-Type: text/html');
                } else {
                    header('Content-Type: text/plain');
                }

                echo $fileContent;

                exit;

            case 'check_requirements':
                $checker = require __DIR__.'/../../SoftwareRequirements/load_checker.php';

                if (isset($_GET['encode-output'])) {
                    header('Content-Type: text/plain');
                    echo str_repeat('-', 25).'BEGIN'.str_repeat('-', 25).PHP_EOL;
                    echo base64_encode(serialize($checker));
                    echo PHP_EOL;
                    echo str_repeat('-', 25).'END'.str_repeat('-', 25).PHP_EOL;
                    exit;
                }

                $majorProblems = $checker->getFailedRequirements();
                $minorProblems = $checker->getFailedRecommendations();
                require __DIR__.'/../../Resources/views/requirements.php';
                exit;

            case 'opcache':
                require __DIR__.'/../../Resources/views/opcache-gui.php';
                exit;

            case 'url_check/path':
                header('Content-Type: text/plain');
                echo 'DP_CHECK_SUCCESS';
                exit;

            case 'logs/errors':
                header('Content-Type: text/plain');

                $path = $logsPath.'/error.log';
                echo "Path: $path\n\n";

                if (file_exists($path)) {
                    echo file_get_contents($path);
                } else {
                    echo '(does not exist)';
                }
                exit;

            case 'logs/updater':
                header('Content-Type: text/plain');

                $path = $logsPath.'/updater.log';
                echo "Path: $path\n\n";

                if (file_exists($path)) {
                    echo file_get_contents($path);
                } else {
                    echo '(does not exist)';
                }
                exit;

            case 'logs/php-errors':
                header('Content-Type: text/plain');

                $path = @ini_get('error_log');
                echo "PHP Error Log Path: $path\n\n";

                if (file_exists($path)) {
                    echo file_get_contents($path);
                } else {
                    echo '(does not exist)';
                }

                echo "\n\n";
                echo str_repeat('*', 72);
                echo "\n\n";

                $path = $logsPath.'/server-php.log';
                echo "PHP Error Log Path (DeskPRO Set): $path\n\n";

                if (file_exists($path)) {
                    echo file_get_contents($path);
                } else {
                    echo '(does not exist)';
                }

                exit;

            case 'update_watcher':
                $DP_AUTH = $this->getAuth();

                /** @var \Symfony\Component\HttpFoundation\Request $request */
                $request = $this->params['request'];

                $BASE_PATH  = rtrim($request->getUriForPath('/'), '/');
                $ASSET_PATH = $request->getUriForPath('/assets/'.$this->env->getAppName().'/web');

                require __DIR__.'/../../Resources/upgrade-watcher/upgrade-watcher.php';

                exit;
        }
    }

    /**
     * @return string|null
     */
    private function getAuth()
    {
        return $this->env->getDatManager()->readTxtFile('server_info_auth', null);
    }

    private function checkAuth($auth)
    {
        // If installed, we require auth
        if (($this->env->getConfig('database.host') || $this->env->getConfig('database.0.host'))) {
            $server_info_auth = $this->getAuth();
            if (!$server_info_auth || empty($auth)) {
                return false;
            }
            if ($auth !== $server_info_auth) {
                return false;
            }
        }

        return true;
    }
}
