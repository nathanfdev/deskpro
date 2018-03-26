<?php

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
