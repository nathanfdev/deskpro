<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
