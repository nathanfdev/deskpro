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
        /** @var \DpEnv $env */
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

    /**
     * Boot a web request.
     */
    public static function bootWeb()
    {
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
}
