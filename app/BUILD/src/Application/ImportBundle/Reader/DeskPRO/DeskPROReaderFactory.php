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

namespace Application\ImportBundle\Reader\DeskPRO;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\BlobStorage\StorageAdapter\DatabaseStorage;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\Reader\ReaderConfigInterface;
use Application\ImportBundle\Reader\ReaderFactoryInterface;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\DependencyInjection\Container;

/**
 * DeskPRO reader factory.
 *
 * Class DeskPROReaderFactory
 */
class DeskPROReaderFactory implements ReaderFactoryInterface
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * Constructor.
     *
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function createReader(ReaderConfigInterface $config)
    {
        if (!$config instanceof DeskPROConfig) {
            throw new \RuntimeException('Config expected to be instance of DeskPROConfig');
        }

        $em = $this->container->getEm()->create(
            DriverManager::getConnection(array(
                'dbname'   => $config->getDatabase(),
                'user'     => $config->getUser(),
                'password' => $config->getPassword(),
                'host'     => $config->getHost(),
                'driver'   => 'pdo_mysql',
            )),
            $this->container->getEm()->getConfiguration()
        );

        $blob_storage = new DeskproBlobStorage($em);
        $db_adapter   = new DatabaseStorage(array(
            'db'                   => $em->getConnection(),
            'table'                => 'blobs_storage',
            'field_name.data'      => 'data',
            'field_name.path'      => 'blob_id',
            'field_name.order'     => 'id',
            'metadata_id_property' => 'blob_id',
        ));

        $blob_storage->addAdapter('db', $db_adapter);

        return new DeskPROReader($config, $em, $blob_storage);
    }

    /**
     * @throws \Exception
     *
     * @return DeskPROConfig
     */
    public static function getDefaultConfig()
    {
        /* @var \DpEnv $DP_ENV */
        global $DP_ENV;
        $config = $DP_ENV->getConfig('import.deskpro_import');

        if (empty($config)) {
            throw new \Exception('DeskPRO import config is not defined');
        }

        return new DeskPROConfig(
            $config['db_host'],
            isset($config['db_port']) ? $config['db_port'] : null,
            $config['db_name'],
            $config['db_username'],
            $config['db_password'],
            @$config['start_ticket_id'] ?: 0
        );
    }
}
