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
     * @param array $tasks
     * @param array $resources
     *
     * @return array
     */
    protected static function runBootTasks(array $tasks, array $resources = [])
    {
        /** @var \DpRun\DpEnv $env */
        $env = $GLOBALS['DP_ENV'];

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

    public static function bootServerInfoChecks()
    {
        /** @var \DpRun\DpEnv $env */
        $env = $GLOBALS['DP_ENV'];

        if ($_GET['__serverinfo'] === 'ping') {
            header('Content-Type: text/plain');
            echo 'pong';
            if ($msg = $env->getDatManager()->readTxtFile('pong_message')) {
                echo "\n";
                echo $msg;
            }
            exit;
        }

        // If installed, we require auth
        if ($env->getConfig('database.host') || $env->getConfig('database.0.host')) {
            $server_info_auth = $env->getDatManager()->readDatFile('server_info_auth', null);
            if (!$server_info_auth || empty($_GET['auth'])) {
                echo "Use the dp:web-server-info command to generate links to view server info.\n";
                exit;
            }
            if ($_GET['auth'] !== $server_info_auth['auth']) {
                echo "The auth code in the URL you are trying to view is invalid. Please run the dp:web-server-info command to generate new links.\n";
                exit;
            }
        }

        switch ($_GET['__serverinfo']) {
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

            default:
                echo 'Unknown serverinfo request.';
        }

        exit;
    }

    /**
     * Boot a web request.
     */
    public static function bootWeb()
    {
        // Makes sure the www path we have in cache
        // is the same as the path we are requesting
        if (defined('DESKPRO_WWW_PATH')) {
            /** @var \DpRun\DpEnv $env */
            $env = $GLOBALS['DP_ENV'];

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

        if (isset($_GET['__serverinfo'])) {
            self::bootServerInfoChecks();

            return;
        }

        $tasks = [
            'Loader',
            'Lib',
            'PreparePaths',
            'Request',
            'HttpKernel',
        ];

        $res = self::runBootTasks($tasks);

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $res['request'];

        /** @var \Symfony\Component\HttpKernel\HttpKernel $kernel */
        $kernel = $res['http_kernel'];

        $response = $kernel->handle($request);
        $response->send();
        $kernel->terminate($request, $response);
    }

    /**
     * Boot a CLI app.
     */
    public static function bootCli()
    {
        $tasks = [
            'Loader',
            'Lib',
            'PreparePaths',
            'CliKernel',
        ];

        $res = self::runBootTasks($tasks);

        /** @var \Symfony\Component\HttpKernel\KernelInterface $kernel */
        $kernel = $res['cli_kernel'];

        /** @var \Symfony\Component\Console\Input\ArgvInput $input */
        $input = $res['cli_input'];

        $app = new Application($kernel);
        $app->run($input);
    }
}
