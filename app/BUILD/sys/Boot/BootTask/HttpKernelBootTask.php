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

namespace DpSys\Boot\BootTask;

use DeskPRO\Bundle\PortalBundle\HttpCache\PortalHttpCache;
use DpSys\Kernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * This creates a HttpKernel based on the currrent request.
 */
class HttpKernelBootTask implements BootTaskInterface
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        /** @var Request $request */
        $request = $resources['request'];

        $interface_id = $this->detectInterfaceId($request);

        switch ($interface_id) {
            case 'agent':
                define('DP_INTERFACE', 'agent');
                define('OLD_AGENT', true);
                break;
            case 'agentv2':
                define('DP_INTERFACE', 'agent');
                break;
            default:
                define('DP_INTERFACE', $interface_id);
        }

        $kernel = $this->getKernelClass(
            $interface_id,
            $env
        );

        return [
            'interface_id' => $interface_id,
            'http_kernel'  => $kernel,
        ];
    }

    /**
     * @param              $interface_id
     * @param \DpRun\DpEnv $env
     *
     * @return PortalHttpCache|Kernel\ApiKernel|Kernel\DpKernel|Kernel\InstallKernel|Kernel\PortalKernel
     */
    private function getKernelClass($interface_id, \DpRun\DpEnv $env)
    {
        switch ($interface_id) {
            case 'apiv2':
                return new Kernel\ApiKernel($env->getEnvId(), $env->isDebug(), $env);
            case 'user':
                $kernel = new Kernel\PortalKernel($env->getEnvId(), $env->isDebug(), $env);

                if (!$env->getConfig('settings.disable_portal_http_cache')) {
                    require_once DP_APP_DIR.'/src/DeskPRO/Bundle/PortalBundle/HttpCache/PortalHttpCache.php';

                    return new PortalHttpCache($kernel, $env->getUserCacheDir().DIRECTORY_SEPARATOR.'http_cache');
                }

                return $kernel;

            case 'install':
                return new Kernel\InstallKernel($env->getEnvId(), $env->isDebug(), $env);
            default:
                return new Kernel\DpKernel($env->getEnvId(), $env->isDebug(), $env);
        }
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function detectInterfaceId(Request $request)
    {
        $path = $request->getPathInfo();

        if (preg_match('#^/agent(/|\?|$)#', $path)) {
            return 'agent';
        } elseif (preg_match('#^/new-agent(/|\?|$)#', $path)) {
            return 'agentv2';
        } elseif (preg_match('#^/adm(in)?(/|\?|$)#', $path)) {
            return 'admin';
        } elseif (preg_match('#^/reports(/|\?|$)#', $path)) {
            return 'reports';
        } elseif (preg_match('#^/api/v2(/|\?|$)#', $path)) {
            return 'apiv2';
        } elseif (preg_match('#^/api(/|\?|$)#', $path)) {
            return 'api';
        } elseif (preg_match('#^/install(/|\?|$)#', $path)) {
            return 'install';
        } else {
            return 'user';
        }
    }
}
