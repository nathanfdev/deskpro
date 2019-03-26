<?php

namespace Application\InstallBundle\Upgrade\Build;

use DpSys\CodePlugin\CodePlugin;
use DpSys\CodePlugin\DpPlugins;
use Symfony\Component\Routing\RouterInterface;

class PostBuildAlways extends AbstractBuild
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->out('Post upgrade-always begin');

        $this->out('Clear tables');
        $tablesByConn = [
            'default' => ['cache', 'result_cache'],
            'system'  => ['notification_system_event', 'system_alerts_events', 'system_alerts_incident_events', 'system_alerts_incidents'],
        ];
        foreach ($tablesByConn as $connName => $tables) {
            $this->execDbQuery($connName, 'SET FOREIGN_KEY_CHECKS = 0');
            foreach ($tables as $t) {
                $this->out("[$connName] Cleaning $t");
                if (defined('DPC_IS_CLOUD')) {
                    // truncate on cloud seems to destabalise galera
                    // probably because truncate is actually a DDL that is the same as DROP/CREATE
                    // so we only do that if there are lotttts of rows
                    if ($this->getDbConnection($connName)->fetchColumn("SELECT COUNT(*) FROM $t") > 2500) {
                        $this->execDbQuery($connName, "TRUNCATE TABLE $t");
                    } else {
                        $this->execDbQuery($connName, "DELETE FROM $t");
                    }
                } else {
                    $this->execDbQuery($connName, "TRUNCATE TABLE $t");
                }
            }
            $this->execDbQuery($connName, 'SET FOREIGN_KEY_CHECKS = 1');
        }

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

        foreach ([
            'cli-phperr.log',
            'server-phperr-web.log',
            'error.log',
            'blob_storage.log',
            'es-indexer.log',
        ] as $l) {
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

        //------------------------------
        // Plugin
        //------------------------------

        DpPlugins::getManager()->runInstallScript(CodePlugin::INSTALL_SCRIPT_POST_UPGRADE, $this->container);

        $this->out('Post upgrade-always done');
    }
}
