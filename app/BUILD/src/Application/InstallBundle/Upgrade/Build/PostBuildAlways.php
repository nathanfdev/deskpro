<?php

namespace Application\InstallBundle\Upgrade\Build;

use Symfony\Component\Routing\RouterInterface;

class PostBuildAlways extends AbstractBuild
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->out('Post upgrade-always begin');

        $this->out('Clear cache table');
        $this->execDbQuery('default', 'TRUNCATE TABLE cache');
        $this->execDbQuery('default', 'TRUNCATE TABLE log_items');
        $this->execDbQuery('default', 'TRUNCATE TABLE result_cache');

        //------------------------------
        // Reset opcache
        //------------------------------

        if (!defined('DPC_IS_CLOUD')) {
            $this->out('Reset OPcache');

            $url = $this->container->getRouter()->generate('sys_serverinfo', [
                'path'  => 'opcache',
                'reset' => '1',
                'auth'  => $this->container->get('deskpro.app_env')->getServerInfoAuth('opcache'),
            ], RouterInterface::ABSOLUTE_URL);

            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]]);
            @file_get_contents($url, null, $ctx);

            $this->out('Warmup OPcache');
            $url = $this->container->getRouter()->generate('sys_serverinfo', [
                'path' => 'opcache/warmup',
                'auth' => $this->container->get('deskpro.app_env')->getServerInfoAuth('opcache/warmup'),
            ], RouterInterface::ABSOLUTE_URL);

            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]]);
            @file_get_contents($url, null, $ctx);
        }

        //------------------------------
        // Clear error logs
        //------------------------------

        foreach (['cli-phperr.log', 'server-phperr-web.log', 'error.log'] as $l) {
            $path = dp_get_log_dir().DIRECTORY_SEPARATOR.$l;
            if (file_exists($path)) {
                $this->out('resetting '.$l);
                @file_put_contents($path, '');
            }
        }

        //------------------------------
        // Clear prod logs
        //------------------------------

        foreach (glob(dp_get_log_dir().DIRECTORY_SEPARATOR.'*-prod.log') as $l) {
            if (file_exists($l)) {
                $this->out('resetting '.pathinfo($l, PATHINFO_BASENAME));
                @file_put_contents($l, '');
            }
        }

        $this->out('Post upgrade-always done');
    }
}
