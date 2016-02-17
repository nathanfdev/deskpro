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

namespace DpSys\Boot;

require_once __DIR__.'/BootTask/BootTaskInterface.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;

class Boot
{
    /**
     * @param \DpRun\DpEnv $env
     * @param array        $tasks
     * @param array        $resources
     *
     * @return array
     */
    public static function runBootTasks(\DpRun\DpEnv $env, array $tasks, array $resources = [])
    {
        $tasks = array_map(function ($t) {
            if (is_array($t)) {
                $classname = $t[0];
                $options = $t[1];
            } else {
                $classname = $t;
                $options = [];
            }

            $classname = $classname.'BootTask';
            $full_classname = 'DpSys\\Boot\\BootTask\\'.$classname;

            if (!class_exists($full_classname, false)) {
                require __DIR__.'/BootTask/'.$classname.'.php';
            }

            return new $full_classname($options);
        }, $tasks);

        /** @var BootTask\BootTaskInterface $t */
        foreach ($tasks as $t) {
            $res = $t->run($env, $resources);
            if ($res && is_array($res)) {
                $resources = array_merge($resources, $res);
            }
        }

        return $resources;
    }

    private static function bootServerInfoChecks($reqName)
    {
        /** @var \DpRun\DpEnv $env */
        $env = $GLOBALS['DP_ENV'];

        if ($reqName === 'ping') {
            header('Content-Type: text/plain');
            echo 'pong';
            if ($msg = $env->getDatManager()->readTxtFile('pong_message')) {
                echo "\n";
                echo $msg;
            }
            exit;
        }

        // If installed, we require auth
        if (($env->getConfig('database.host') || $env->getConfig('database.0.host'))) {
            $server_info_auth = $env->getDatManager()->readTxtFile('server_info_auth', null);
            if (!$server_info_auth || empty($_GET['auth'])) {
                echo "Use the dp:web-server-info command to generate links to view server info.\n";
                exit;
            }
            if ($_GET['auth'] !== $server_info_auth) {
                echo "The auth code in the URL you are trying to view is invalid. Please run the dp:web-server-info command to generate new links.\n";
                exit;
            }
        }

        switch ($reqName) {
            case 'phpinfo':
                phpinfo();
                break;

            case 'check_requirements':
                $checker = require __DIR__.'/../SoftwareRequirements/load_checker.php';

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
                require __DIR__.'/../Resources/views/requirements.php';
                break;

            case 'url_check/path':
                header('Content-Type: text/plain');
                echo 'DP_CHECK_SUCCESS';
                break;

            default:
                echo 'Unknown serverinfo request.';
        }

        exit;
    }

    /**
     * Boot a web request.
     */
    public static function bootWeb(\DpRun\DpEnv $env)
    {
        #------------------------------
        # verify www path
        #------------------------------

        // Makes sure the www path we have in cache
        // is the same as the path we are requesting
        if (defined('DESKPRO_WWW_PATH')) {
            if ($env) {
                // Check if its a non-default path...
                if (DESKPRO_WWW_PATH !== $env->getDpRoot().DIRECTORY_SEPARATOR.'www') {
                    $current = $env->getDatManager()->readTxtFile('www_dir', null);
                    if (!$current || $current !== DESKPRO_WWW_PATH) {
                        $env->getDatManager()->writeTxtFile('www_dir', DESKPRO_WWW_PATH);
                    }
                // If it IS the default path, then we just
                // make sure to remove the cache file.
                } else {
                    $env->getDatManager()->removeTxtFile('www_dir');
                }
            }
        }

        #------------------------------
        # Boot request and handle serverinfo reqs
        #------------------------------

        // An 'early' serverinfo request
        if (isset($_GET['__serverinfo'])) {
            self::bootServerInfoChecks($_GET['__serverinfo']);

            return;
        }

        $tasks = [
            'Loader',
            'Lib',
            'PreparePaths',
            'Request',
        ];

        $res = self::runBootTasks($env, $tasks);

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $res['request'];

        // We can also enter serverinfo via a path,
        // this is usually only used when we need to test URL rewriting
        $path = '/'.ltrim($request->getPathInfo(), '/');
        if (substr($path, 0, 14) === '/__serverinfo/') {
            self::bootServerInfoChecks(substr($path, 14));

            return;
        }

        #------------------------------
        # Boot to low scripts
        #------------------------------

        $lowClass = null;
        if (substr($path, 0, 7) === '/dp.php' && (!isset($path[7]) || $path[7] === '/')) {
            $lowClass = 'DpSys\\LowScript\\DpScript';
        } elseif (substr($path, 0, 9) === '/file.php' && (!isset($path[9]) || $path[9] === '/')) {
            $lowClass = 'DpSys\\LowScript\\ServeFileScript';
        } elseif (substr($path, 0, 17) === '/get_messages.php' && (!isset($path[17]) || $path[17] === '/')) {
            $lowClass = 'DpSys\\LowScript\\GetMsgScript';
        }

        if ($lowClass) {
            /** @var \DpSys\LowScript\LowScriptAbstract $lowScript */
            $lowScript = new $lowClass($env, $request);
            $lowScript->run();

            return;
        }

        #------------------------------
        # Boot to a normal symfony request
        #------------------------------

        $res = self::runBootTasks($env, ['HttpKernel'], $res);

        /** @var \Symfony\Component\HttpKernel\HttpKernel $kernel */
        $kernel = $res['http_kernel'];

        $response = $kernel->handle($request);
        $response->send();
        $kernel->terminate($request, $response);
    }

    /**
     * Boot a CLI app.
     */
    public static function bootCli(\DpRun\DpEnv $env)
    {
        $tasks = [
            'Loader',
            'Lib',
            'PreparePaths',
            'CliKernel',
        ];

        $res = self::runBootTasks($env, $tasks);

        /** @var \Symfony\Component\HttpKernel\KernelInterface $kernel */
        $kernel = $res['cli_kernel'];

        /** @var \Symfony\Component\Console\Input\ArgvInput $input */
        $input = $res['cli_input'];

        $app = new Application($kernel);
        $app->run($input);
    }

    /**
     * Boot cron app.
     */
    public static function bootCron(\DpRun\DpEnv $env)
    {
        $tasks = [
            'Loader',
            'Lib',
            'PreparePaths',
            'CliKernel',
        ];

        $res = self::runBootTasks($env, $tasks);

        /** @var \Symfony\Component\HttpKernel\KernelInterface $kernel */
        $kernel = $res['cli_kernel'];

        /** @var \Symfony\Component\Console\Input\ArgvInput $input */
        $input = $res['cli_input'];

        $app = new Application($kernel);
        $app->run($input);
    }
}
