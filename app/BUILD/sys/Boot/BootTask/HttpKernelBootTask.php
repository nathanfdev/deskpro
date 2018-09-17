<?php

namespace DpSys\Boot\BootTask;

use DeskPRO\Bundle\PortalBundle\HttpCache\PortalHttpCache;
use DpRun\DpEnv;
use DpSys\Kernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * This creates a HttpKernel based on the currrent request.
 */
class HttpKernelBootTask implements BootTaskInterface
{
    public function run(DpEnv $env, array $resources)
    {
        /** @var Request $request */
        $request = $resources['request'];

        if (isset($resources['interface_id'])) {
            $interface_id = $resources['interface_id'];
        } else {
            $interface_id = $this->detectInterfaceId($request);
        }

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
            $request,
            $interface_id,
            $env
        );

        return [
            'interface_id' => $interface_id,
            'http_kernel'  => $kernel,
        ];
    }

    /**
     * @param Request $request
     * @param string  $interface_id
     * @param DpEnv   $env
     *
     * @return PortalHttpCache|Kernel\ApiKernel|Kernel\DpKernel|Kernel\InstallKernel|Kernel\PortalKernel
     */
    private function getKernelClass(Request $request, $interface_id, DpEnv $env)
    {
        switch ($interface_id) {
            case 'messenger':
                return new Kernel\MessengerKernel($env->getEnvId(), $env->isDebug(), $env);
            case 'apiv2':
                return new Kernel\ApiKernel($env->getEnvId(), $env->isDebug(), $env);
            case 'user':
                $kernel = new Kernel\PortalKernel($env->getEnvId(), $env->isDebug(), $env);

                if (!$env->getConfig('settings.disable_portal_http_cache')) {
                    require_once DP_APP_DIR.'/src/DeskPRO/Bundle/PortalBundle/HttpCache/PortalHttpCache.php';

                    return new PortalHttpCache(
                        $kernel,
                        $env->getUserCacheDir().DIRECTORY_SEPARATOR.'http_cache'.DIRECTORY_SEPARATOR.$env->getAppName(),
                        $request->getBasePath()
                    );
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

        if ($this->isUrlSegmentPrefix($path, '/dp')
            || $path === '/favicon.ico'
            || $path === '/sitemap.xml'
            || $path === '/robots.txt'
        ) {
            return 'user';
        }

        if ($this->isUrlSegmentPrefix($path, '/api/messenger/user')) {
            return 'messenger';
        }
        if ($this->isUrlSegmentPrefix($path, '/agent') || $this->isUrlSegmentPrefix($path, '/scripts/agent')) {
            return 'agent';
        }
        if ($this->isUrlSegmentPrefix($path, '/new-agent')) {
            return 'agentv2';
        }
        if ($this->isUrlSegmentPrefix($path, '/admin')) {
            return 'admin';
        }
        if ($this->isUrlSegmentPrefix($path, '/cloud')) {
            return 'admin';
        }
        if ($this->isUrlSegmentPrefix($path, '/reports')) {
            return 'reports';
        }
        if ($this->isUrlSegmentPrefix($path, '/api/v2')) {
            return 'apiv2';
        }
        if ($this->isUrlSegmentPrefix($path, '/api')) {
            return 'api';
        }

        return 'user';
    }

    /**
     * Checks $path to see if it has $seg prefix.
     *
     * <code>
     * isUrlSegmentPrefix('/foo/bar/baz', '/foo');
     * // roughly same as regex:
     * // (but we use this func as a micro-optimisation)
     * preg_match('/^\/foo(\/|$)/', '/foo/bar/baz')
     * </code>
     *
     * @param string $path The request path
     * @param string $seg  The URL prefix we are testing for
     *
     * @return bool
     */
    private function isUrlSegmentPrefix($path, $seg)
    {
        $len = strlen($seg);

        if (
            // has at least that many chars
            isset($path[$len - 1])

            // has that prefix
            && substr($path, 0, $len) === $seg

            // Make sure nothing else (other than a slash) after the prefix
            // e.g. this is so matching '/foo' matches '/foo/' but not '/foobar'
            && (
                !isset($path[$len])
                || $path[$len] === '/'
            )
        ) {
            return true;
        }

        return false;
    }
}
