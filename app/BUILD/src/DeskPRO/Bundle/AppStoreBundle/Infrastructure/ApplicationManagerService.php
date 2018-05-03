<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use Application\DeskPRO;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\App;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppAssetBlob;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use Doctrine\ORM\EntityManager;
use Orb\Data\ContentTypes;

/**
 * Class ApplicationManagerService.
 */
class ApplicationManagerService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @var EntityIdentityMapResolver
     */
    private $entityResolver;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blobStorage)
    {
        $this->em             = $em;
        $this->blobStorage    = $blobStorage;
        $this->entityResolver = new EntityIdentityMapResolver($em);
    }

    /**
     * @param AppAssetBlob $asset
     *
     * @return Domain\AppManifest
     */
    public function readManifestFromAssetBlob(AppAssetBlob $asset)
    {
        // TODO this should be moved into DI container
        AppAssetBlob::setBlobStorageService($this->blobStorage);
        $reader = new AppManifestReader();

        return $reader->readManifestFromJson($asset->getRawContent());
    }

    /**
     * Returns the name of the strategy that must be applied when removing $instance.
     *
     * When this instance is the last one we want to also remove the app itself
     *
     * @param AppInstance $instance
     *
     * @return string
     */
    public function getRemoveStrategy(AppInstance $instance)
    {
        $appId      = $instance->getApplicationId();
        $instanceId = $instance->getId();
        if (!$appId || !$instanceId) {
            return 'none';
        }

        // perhaps should check that app instance is still linked to App in the db, but at this point we can
        // be pretty certain of this fact
        $qb    = $this->em->createQueryBuilder();
        $query = $qb->select('i.id')
            ->from(AppInstance::class, 'i')
            ->where('i.app = :appId')
            ->andWhere('i.id <> :id')
            ->setParameter('appId', $appId)
            ->setParameter('id', $instanceId)
            ->getQuery()
        ;

        $otherId = $query->setMaxResults(1)->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SCALAR);
        if (is_null($otherId)) {
            return 'last-instance';
        }

        return 'instance';
    }

    /**
     * @param Domain\AppBundle $bundle
     *
     * @return Domain\ApplicationInstance
     */
    public function createFirstInstance(Domain\AppBundle $bundle)
    {
        $appEntity      = $this->createOrUpdateAppEntity($bundle);
        $instanceEntity = $this->createInstance($appEntity);

        return $instanceEntity;
    }

    /**
     * @param Domain\AppBundle $bundle
     *
     * @return string
     */
    public function findAppForBundle(Domain\AppBundle $bundle)
    {
        $manifestReader = new Infrastructure\AppManifestReader();
        $manifest       = $manifestReader->readManifestFromJson($bundle->getManifestAsString());

        $app = $this->em->getRepository(App::class)->findOneBy(['name' => $manifest->getName()]);

        return $app ? $app : null;
    }

    /**
     * @param Domain\AppBundle $bundle
     *
     * @return InstallBundleDetails
     */
    public function installBundle(Domain\AppBundle $bundle)
    {
        $app         = $this->findAppForBundle($bundle);
        $installType = $app ? InstallBundleDetails::INSTALL_TYPE_UPGRADE : InstallBundleDetails::INSTALL_TYPE_INSTALL;

        if (!$app) {
            $manifestReader = new Infrastructure\AppManifestReader();
            $manifest       = $manifestReader->readManifestFromJson($bundle->getManifestAsString());

            $app = new App();
            $app->setName($manifest->getName());
        }

        $app->setManifest(json_decode($bundle->getManifestAsString(), true));
        $this->updateAssets($app, $bundle);

        return new InstallBundleDetails($app, $installType);
    }

    /**
     * @param Domain\AppBundle $bundle
     *
     * @return App
     */
    public function createOrUpdateAppEntity(Domain\AppBundle $bundle)
    {
        $app = $this->findAppForBundle($bundle);
        if (!$app) {
            $app = new App();

            $manifestReader = new Infrastructure\AppManifestReader();
            $manifest       = $manifestReader->readManifestFromJson($bundle->getManifestAsString());
            $app->setName($manifest->getName());
        }

        $app->setManifest(json_decode($bundle->getManifestAsString(), true));

        $this->updateAssets($app, $bundle);

        return $app;
    }

    /**
     * @param App              $app
     * @param Domain\AppBundle $bundle
     *
     * @return App
     */
    private function updateAssets(App $app, Domain\AppBundle $bundle)
    {
        $app->setManifest(json_decode($bundle->getManifestAsString(), true));
        $new      = [];
        $removals = [];

        // TODO move into service method
        $instanceCount = count($app->getInstances());

        foreach ($app->getAssets() as $asset) {
            if ($instanceCount) {
                // save the previous manifest into .deskpro/manifest.json.prev only if we have any instances
                if ($asset->getPath() === 'manifest.json') {
                    // TODO this should be moved into DI container
                    AppAssetBlob::setBlobStorageService($this->blobStorage);
                    $new[]      = $asset->copy('.deskpro/versions/manifest.json.prev');
                    $removals[] = $asset;
                } elseif ($asset->getPath() === '.deskpro/versions/manifest.json.prev') {
                    $removals[] = $asset;
                } elseif (substr($asset->getPath(), 0, strlen('.deskpro/')) !== '.deskpro/') {
                    $removals[] = $asset;
                }
            } else {
                $removals[] = $asset;
            }
        }

        foreach ($removals as $asset) {
            $app->getAssets()->removeElement($asset);
        }
        foreach ($new as $asset) {
            $app->addAsset($asset);
        }

        foreach ($bundle->listAllResources() as $resource) {
            $fileExtension = $resource->getFileExtension();
            $contentType   = ContentTypes::getContentTypeFromExtension($fileExtension);
            if (empty($contentType)) {
                // could be a problem for js.map files which get ther file extension as map instead of js.map,
                // but see http://stackoverflow.com/questions/19911929/what-mime-type-should-i-use-for-javascript-source-map-files
                $contentType = 'application/octet-stream';
            }

            $blob         = $this->blobStorage->createBlobRecordFromString($resource->getContent(), $resource->getPath(), $contentType);
            $specialAsset = new AppAssetBlob();
            $specialAsset->setPath($resource->getPath());
            $specialAsset->setBlob($blob);

            $app->addAsset($specialAsset);
        }

        foreach ($removals as $asset) {
            $this->em->remove($asset);
        }
        $this->em->persist($app);
        $this->em->flush();

        return $app;
    }

    /**
     * @param App        $app
     * @param array|null $settings
     *
     * @return AppInstance
     */
    public function createInstance(App $app, array $settings = [])
    {
        $instance = new AppInstance();
        $instance
            ->setIsInstalled(false)
            ->setApp($app)
            ->setName($app->getManifest()->getTitle())
            ->setSettings($settings)
        ;

        $this->em->persist($instance);
        $this->em->flush();

        return $instance;
    }
}
