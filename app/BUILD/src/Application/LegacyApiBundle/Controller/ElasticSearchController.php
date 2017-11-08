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

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\ApacheTika\ClientManager;
use Application\DeskPRO\Elastica\ClientFactory;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Monolog\Logger;
use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Elastica\Response;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use Orb\Util\Numbers;

/**
 * @ApiModes("all")
 */
class ElasticSearchController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // get-settings
    //###################################################################################################################

    public function getSettingsAction()
    {
        $values = [
            'enabled'        => (bool) $this->settings->get('elastica.enabled'),
            'requires_reset' => (bool) $this->settings->get('elastica.requires_reset'),
            'url'            => $this->settings->get('elastica.clients.default.url'),
            'tika_enabled'   => (bool) $this->settings->get('elastica.tika.enabled'),
            'tika_ip'        => $this->settings->get('elastica.tika.ip_address'),
            'tika_port'      => $this->settings->get('elastic_settings.tika_port'),
        ];

        return $this->createApiResponse(['elastic_settings' => $values]);
    }

    //###################################################################################################################
    // save-settings
    //###################################################################################################################

    public function saveSettingsAction()
    {
        $wasEnabled = $this->settings->get('elastic_settings.enabled');

        $url = $this->in->getString('elastic_settings.url');

        // A missing trailing slash causes errors
        if (!preg_match('|/$|', $url)) {
            $url .= '/';
        }

        $this->settings->setSetting('elastica.enabled', $this->in->getBoolInt('elastic_settings.enabled'));
        $this->settings->setSetting('elastica.clients.default.url', $url ?: '');
        $this->settings->setSetting('elastica.tika.enabled', $this->in->getBoolInt('elastic_settings.tika_enabled') ?: '');
        $this->settings->setSetting('elastica.tika.ip_address', $this->in->getString('elastic_settings.tika_ip') ?: '');
        $this->settings->setSetting('elastica.tika.port', $this->in->getInt('elastic_settings.tika_port') ?: '9998');

        if ($this->in->getBoolInt('elastic_settings.enabled')) {
            try {
                $this->checkVersion($this->in->getString('elastic_settings.url'));
            } catch (\Exception $e) {
                $this->settings->setSetting('elastica.enabled', false);

                return $this->createApiErrorResponse('', $e->getMessage(), 401);
            }
        }

        // Just turned on, we need to toggle the requires_reset flag
        if ((!$wasEnabled || $this->in->getBool('reindex')) && $this->in->getBool('elastic_settings.enabled')) {
            $this->settings->setSetting('elastica.requires_reset', 1);
            $esStatus = $this->em->getRepository(DataStore::class)->getByName('sys.es_indexer', false);
            if ($esStatus) {
                $this->em->remove($esStatus);
                $this->em->flush();
            }
        } else {
            $this->settings->setSetting('elastica.requires_reset', null);
        }

        return $this->createApiSuccessResponse();
    }

    //###################################################################################################################
    // test-settings
    //###################################################################################################################

    public function testSettingsAction()
    {
        //------------------------------
        // Configure logger
        //------------------------------

        try {
            $config = ClientFactory::createConfigFromUrl($this->in->getString('url'));
        } catch (\Exception $e) {
            return $this->createApiResponse(['is_success' => false, 'log' => $e->getMessage()]);
        }

        $logger = new Logger('elastic_test');
        $logger->enableSavedMessages();

        $elastica_logger = new ElasticaLogger($logger);
        $elastica_logger->debug(sprintf('URL: %s', $this->in->getString('url')));
        $elastica_logger->debug(sprintf('Host: %s', $config['host']));
        $elastica_logger->debug(sprintf('Port: %s', $config['port']));
        $elastica_logger->debug(sprintf('Path: %s', $config['path']));
        $elastica_logger->debug(sprintf('Transport: %s', $config['transport']));

        $config['logger'] = $elastica_logger;

        //------------------------------
        // Create client
        //------------------------------

        /** @var \Application\DeskPRO\Elastica\ClientFactory $client_factory */
        $client_factory = $this->container->get('deskpro.elastica.client_factory');

        $client = $client_factory->createClientByConfig($config);
        //------------------------------
        // Test client
        //------------------------------

        $ts_start = microtime(true);
        $error    = false;

        try {
            $elastica_logger->debug('checking version');
            $this->checkVersion($this->in->getString('url'));

            $elastica_logger->debug('Fetching status...');
            $status = $client->getStatus();
            $elastica_logger->debug(sprintf('Done in %.3fs', microtime(true) - $ts_start));

            if ($status->getResponse()->isOk()) {
                $elastica_logger->info('Success. Response: '.json_encode($status->getResponse()->getData()));
            } else {
                $elastica_logger->error('Failed: '.$status->getResponse()->getError());
                $error = true;
            }
        } catch (\Exception $e) {
            $elastica_logger->error("Exception: {$e->getMessage()}");
            $error = true;
        }

        if (!$error && $this->in->getBoolInt('tika_enabled')) {
            try {
                /** @var ClientManager $tika_client_manager */
                $tika_client_manager = $this->container->get('deskpro.apache_tika.client_manager');
                $config              = $tika_client_manager->createConfigFromUrl(
                    $this->in->getString('tika_ip'),
                    $this->in->getString('tika_port')
                );
                $tika_client = $tika_client_manager->getClientFromConfig($config);

                $version = $tika_client->request('version');
                $elastica_logger->debug('Apache Tika');
                $elastica_logger->debug('checking version');
                $elastica_logger->debug('Version: '.$version);
            } catch (\Exception $e) {
                $elastica_logger->error("Exception: {$e->getMessage()}");
                $error = true;
            }
        }

        return $this->createApiResponse([
            'is_success' => !$error,
            'log'        => $logger->getSavedMessages(),
        ]);
    }

    protected function checkVersion($url)
    {
        if (!$url) {
            return;
        }
        /** @var Elasticsearch $elasticSearch */
        $elasticSearch = $this->container->get('deskpro.search_manager.elasticsearch');
        $elasticSearch->testVersion($url);
    }

    //###################################################################################################################
    // index-status
    //###################################################################################################################

    public function indexStatusAction()
    {
        $esStatus = $this->em->getRepository(DataStore::class)->getByName('sys.es_indexer', false);

        $statusData = $esStatus ? $esStatus->data : [];

        $logPath = dp_get_log_dir().'/es-indexer.log';
        $log     = null;
        if (file_exists($logPath)) {
            $log = @file_get_contents($logPath);
        }

        $isIndexing = ($this->getContainer()->getSetting('elastica.requires_reset') || ($esStatus && $esStatus->getData('status') == 'running'));

        if (($esStatus && $esStatus->getData('status') == 'running') && isset($statusData['date_last'])) {
            if ($statusData['date_last']->getTimestamp() < (time() - 1200)) {
                $statusData['status'] = 'crashed';
            }
        }

        $info = null;
        if (!$isIndexing) {
            try {
                /** @var \Elastica\Index $index */
                $index     = $this->getContainer()->get('fos_elastica.index.deskpro');
                $indexName = $index->getName();

                $stats = $index->request('_stats', 'GET')->getData();

                if (!isset($stats['indices'][$indexName])) {
                    $info = ['error' => 'no_index'];
                } else {
                    $info = [
                        'size'          => @$stats['indices'][$indexName]['total']['store']['size_in_bytes'],
                        'size_readable' => Numbers::filesizeDisplay(@$stats['indices'][$indexName]['total']['store']['size_in_bytes']),
                        'num_docs'      => @$stats['indices'][$indexName]['total']['docs']['count'],
                    ];
                }
            } catch (\Exception $e) {
                $info = ['error' => 'no_status'];
            }
        }

        if (empty($info['error']) && isset($index) && isset($indexName)) {
            $types = [
                'feedback'          => 'feedback',
                'organization'      => 'organizations',
                'person'            => 'people',
                'article'           => 'articles',
                'ticket'            => 'tickets',
                'news'              => 'news',
                'download'          => 'downloads',
                'chat_conversation' => 'chat_conversations',
                'topic'             => 'topics',
            ];

            foreach ($types as $type => $table) {
                try {
                    $count = $index->request("$type/_count", 'GET')->getData();
                    if (isset($count['count'])) {
                        $info["num_$type"]     = $count['count'];
                        $info["realnum_$type"] = $this->db->fetchColumn("SELECT COUNT(*) FROM $table");
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return $this->createJsonResponse([
            'is_indexing'    => $isIndexing,
            'indexer_status' => $statusData ? $statusData : null,
            'indexer_log'    => $log ?: null,
            'info'           => $info,
        ]);
    }
}
