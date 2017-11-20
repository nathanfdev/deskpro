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
     * @param AppInstance $instance
     * @param string      $strategy
     */
    public function remove(AppInstance $instance, $strategy)
    {
        $entity = null;
        /** @var DeskPRO\Entity\Blob $entities */
        $entities = [];
        $blobs    = [];
        if ($strategy === 'instance') {
            $entities[] = $instance;
        } elseif ($strategy === 'last-instance') {
            $entities[] = $instance;

            $app = $instance->getApp();
            foreach ($app->getAssets() as $asset) {
                if ($asset->getPath() === '.deskpro/versions/manifest.json.prev') {
                    $entities[] = $asset;
                    $blobs[]      = $asset->getBlob();
                }
            }
        } else {
            $msg = sprintf('Could not handle remove strategy: %s', $strategy);
            throw new \DomainException($msg);
        }

        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }
        $this->em->flush();

        // delete the blobs, one by one :(
        foreach ($blobs as $blob) {
            $this->blobStorage->deleteBlobRecord($blob);
        }
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
     * @return App
     */
    public function createOrUpdateAppEntity(Domain\AppBundle $bundle)
    {
        $manifestReader = new Infrastructure\AppManifestReader();
        $manifest       = $manifestReader->readManifestFromJson($bundle->getManifestAsString());

        $app = $this->em->getRepository(App::class)->findOneBy(['name' => $manifest->getName()]);
        if (!$app) {
            $app = new App();
        }

        // TODO move into service method
        $instanceCount = count($app->getInstances());

        $app->setManifest(json_decode($bundle->getManifestAsString(), true));
        $app->setName($manifest->getName());

        $specialAssets = [];

        // save the previous manifest into .deskpro/manifest.json.prev only if we have any instances
        if ($instanceCount) {
            // TODO this should be moved into DI container
            AppAssetBlob::setBlobStorageService($this->blobStorage);
            foreach ($app->getAssets() as $specialAsset) {
                if ($specialAsset->getPath() === 'manifest.json') {
                    $specialAssets[] = $specialAsset->copy('.deskpro/versions/manifest.json.prev');
                }
                $app->getAssets()->removeElement($specialAsset);
            }
        }

        foreach ($specialAssets as $specialAsset) {
            $app->addAsset($specialAsset);
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
            ->setScope($app->getManifest()->getScope())
        ;

        $this->em->persist($instance);
        $this->em->flush();

        return $instance;
    }
}
