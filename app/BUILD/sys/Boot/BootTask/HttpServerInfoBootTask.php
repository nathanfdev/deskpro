<?php

namespace DpSys\Boot\BootTask;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Bundle\UpdateBundle\Session\UpdateSessionManager;
use DpRun\LowUtil;
use Orb\Util\Dates;
use Symfony\Component\Finder\Finder;

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
        $this->authlessServerChecks($env, $action);

        if (!$this->checkAuth($auth, $action)) {
            echo "The auth code in the URL you are trying to view is invalid. Please run the dp:web-server-info command to generate new links.\n";
            echo "See: https://support.deskpro.com/kb/articles/553\n";
            exit;
        }

        // Will exit if any match
        $this->authRequiredServerChecks($action);

        echo 'Unknown serverinfo request.';
        exit;
    }

    private function authlessServerChecks(\DpRun\DpEnv $env, $action)
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
                    echo preg_replace('/[^a-zA-Z0-9\._]/', '', $_GET['callback']);
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
                echo 'HTTP_METHOD_'.htmlspecialchars_decode(strtoupper(@$_SERVER['REQUEST_METHOD']));
                exit;

            case 'version':
            case 'version.json':
                $appEnv = new AppEnv($env);

                $time = $appEnv->getBuildTime();
                if ($time) {
                    $date = date('Y-m-d H:i:s', $time);
                } else {
                    $date = 'n/a';
                }

                if ($action === 'version.json') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'version'   => $appEnv->getVersionName(),
                        'buildId'   => $appEnv->getBuildId(),
                        'buildTime' => $time ? $date : null,
                    ], \JSON_PRETTY_PRINT);
                } else {
                    header('Content-Type: text/plain');
                    echo "Version:    {$appEnv->getVersionName()}\n";
                    echo "Build ID:   {$appEnv->getBuildId()}\n";
                    echo "Build Time: {$date}\n";
                }
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

            case 'php_version':
                header('Content-Type: text/plain');
                echo phpversion();
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
                if (extension_loaded('Zend OPcache')) {
                    require __DIR__.'/../../Resources/views/opcache-gui.php';
                } else {
                    echo "OPcache not enabled\n";
                }
                exit;
            case 'opcache/warmup':
                if (extension_loaded('Zend OPcache')) {
                    $this->warmupOpCache();
                    echo "OK\n";
                } else {
                    echo "OK (no-op, OPcache not enabled)\n";
                }
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
                /** @var \Symfony\Component\HttpFoundation\Request $request */
                $request = $this->params['request'];

                if (isset($_GET['status'])) {
                    return $this->authRequiredServerChecks('update_watcher_status');
                }

                $DP_AUTH    = $this->getAuth();
                $BASE_URL   = rtrim($request->getUriForPath('/'), '/');
                $BASE_PATH  = rtrim($request->getBasePath(), '/');
                $ASSET_URL  = $request->getUriForPath('/assets/'.$this->env->getAppName().'/web');
                $ASSET_PATH = rtrim($request->getBasePath(), '/').'/assets/'.$this->env->getAppName().'/web';

                require __DIR__.'/../../Resources/upgrade-watcher/upgrade-watcher.php';

                exit;

            case 'update_watcher_status':
                $sessionId = $this->env->getDatManager()->readTxtFile('last_updater_session_id', null);

                header('Content-Type: application/json');

                $pdo         = LowUtil::getPdoFromMysqlInfo($this->env->getConfig('database'));
                $q           = $pdo->query("SELECT value FROM settings WHERE name = 'auto_updater_next_check'");
                $nextDateStr = $q->fetchColumn();
                $nextDate    = $nextDateStr ? \DateTime::createFromFormat('Y-m-d H:i:s', $nextDateStr) : null;

                // If there is no session yet, then we check if we're waiting for it
                if (!$sessionId) {
                    if (!$nextDateStr) {
                        echo json_encode(['status' => 'none']);
                        exit;
                    }

                    echo json_encode([
                        'status'           => 'waiting',
                        'date'             => $nextDate->format('Y-m-d H:i:s'),
                        'date_description' => ($nextDate < (new \DateTime())) ? 'in a few seconds' : Dates::secsToReadable($nextDate->getTimestamp() - time()),
                    ]);
                    exit;
                }

                try {
                    $sm      = new UpdateSessionManager($sessionId, $this->env->getUserTmpDir());
                    $session = $sm->getSession();
                } catch (\Exception $e) {
                    echo json_encode(['status' => 'none']);
                    exit;
                }

                $data = [
                    'status'         => '',
                    'finishedStatus' => null,
                    'summary'        => $session->getSummary() ?: '',
                    'details'        => $session->getDetails() ?: '',
                    'steps'          => [],
                    'currentStepId'  => $session->findCurrentStepId(),
                    'next'           => [
                        'date'             => $nextDate ? $nextDate->format('Y-m-d H:i:s') : '',
                        'date_description' => $nextDate ? (($nextDate < (new \DateTime())) ? 'in a few seconds' : Dates::secsToReadable($nextDate->getTimestamp() - time())) : null,
                    ],
                ];

                foreach ($session->getStepIds() as $stepId) {
                    $step = $session->getStep($stepId);

                    if ($step->isRunning()) {
                        $stepStatus = 'running';
                    } elseif ($step->isError()) {
                        $stepStatus = 'error';
                    } elseif ($step->isFinished()) {
                        $stepStatus = 'finished';
                    } else {
                        $stepStatus = 'waiting';
                    }

                    $data['steps'][] = [
                        'status'  => $stepStatus,
                        'stepId'  => $stepId,
                        'title'   => $step->getTitle(),
                        'summary' => $step->getSummary(),
                        'details' => $step->getDetails(),
                    ];
                }

                if ($session->isSuccess()) {
                    $data['finishedStatus'] = 'success';
                } elseif ($session->isError()) {
                    $data['finishedStatus'] = 'error';
                } elseif ($session->isWaiting()) {
                    $data['finishedStatus'] = 'warning';
                }

                if ($session->isWaiting()) {
                    $nextDate                 = new \DateTime();
                    $data['status']           = 'waiting';
                    $data['date']             = $nextDate->format('Y-m-d H:i:s');
                    $data['date_description'] = 'in a few seconds';
                    exit;
                } elseif ($session->isRunning()) {
                    $data['status'] = 'running';
                } elseif ($session->isFinished()) {
                    $data['status'] = 'finished';
                }

                echo json_encode($data);

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

    private function checkAuth($auth, $action)
    {
        // If installed, we require auth
        if (($this->env->getConfig('database.host') || $this->env->getConfig('database.0.host'))) {
            $server_info_auth = $this->getAuth();
            if (!$server_info_auth || empty($auth)) {
                return false;
            }
            if ($auth !== $server_info_auth && $auth !== sha1($server_info_auth.$action)) {
                return false;
            }
        }

        return true;
    }

    private function warmupOpCache()
    {
        if (!extension_loaded('Zend OPcache')) {
            return;
        }

        $dir          = $this->env->getAppDir();
        $filelistFile = $dir.'/sys/Resources/serverinfo/warmupit.php';

        // warmupit.php is generated at build time in the 'build-prod' script
        // devs wont have this, so we need to skip
        if (!is_file($filelistFile)) {
            return;
        }

        $included = array_flip(get_included_files());
        foreach (require($filelistFile) as $file) {
            if (is_file($dir.$file) && !isset($included[$dir.DIRECTORY_SEPARATOR.$file])) {
                @opcache_compile_file($dir.DIRECTORY_SEPARATOR.$file);
            }
        }

        $dirs = array_filter([
            $this->env->getDpRoot().'/app/run',
            $this->env->getAppBaseKernelCacheDir(),
        ], function ($d) {
            return is_dir($d);
        });

        $finder = Finder::create()->in($dirs)->name('*.php');
        /** @var \SplFileInfo $f */
        foreach ($finder as $f) {
            if (!isset($included[$f->getRealPath()])) {
                @opcache_compile_file($f->getRealPath());
            }
        }
    }
}
