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

namespace DeskPRO\Bundle\ImportBundle\Storage\StorageAdapter;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StorageAdapterFactory.
 */
class StorageAdapterFactory
{
    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param AppEnvInterface    $appEnv
     * @param ContainerInterface $container
     */
    public function __construct(AppEnvInterface $appEnv, ContainerInterface $container)
    {
        $this->appEnv    = $appEnv;
        $this->container = $container;
    }

    /**
     * @return StorageAdapterInterface
     */
    public function create()
    {
        $adapterId = $this->appEnv->getConfig('settings.importer_storage_adapter', 'filesystem');
        if (!$this->container->has('dp.importer.storage_adapter.'.$adapterId)) {
            throw new \RuntimeException("Importer storage `$adapterId` not found");
        }

        return $this->container->get('dp.importer.storage_adapter.'.$adapterId);
    }

    /**
     * @return FilesystemAdapter
     */
    public function createFilesystemAdapter()
    {
        $basePath = rtrim($this->appEnv->getUserFilesDir(), '/').'/import';
        $adapter  = new FilesystemAdapter($basePath);

        return $adapter;
    }

    /**
     * @return AmazonS3Adapter
     */
    public function createAmazonS3Adapter()
    {
        $settingsBag = $this->container->get('settings_resolver')->getGlobalSettings();

        return new AmazonS3Adapter(
            $settingsBag->get('core.filestorage_s3_basepath').'/import',
            $this->container->get('amazon_s3_client'),
            $settingsBag->get('core.filestorage_s3_bucket')
        );
    }
}
