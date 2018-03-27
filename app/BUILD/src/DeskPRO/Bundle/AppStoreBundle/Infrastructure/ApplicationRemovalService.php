<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\App;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;

/**
 * Handles the removal process for an application
 */
class ApplicationRemovalService
{
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
     * @param AppInstance $instance
     * @param string      $strategy
     */
    public function remove(AppInstance $instance, $strategy)
    {
        $entity = null;
        /** @var Blob $entities */
        $entities = [];
        if ($strategy === 'instance') {
            $entities[] = $instance;
        } elseif ($strategy === 'last-instance') {
            $entities[] = $instance;

            $app = $instance->getApp();
            $this->removeAssets($app);
        } else {
            $msg = sprintf('Could not handle remove strategy: %s', $strategy);
            throw new \DomainException($msg);
        }

        $renameFieldsQueries = $this->getRenameCustomFieldsQueries($instance);

        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }
        $this->em->flush();

        // this is not a critical section so we should treat exceptions thrown here a bit different
        foreach ($renameFieldsQueries as $query) {
            $query->execute();
        }
    }

    /**
     * @return Query[]
     */
    private function getRenameCustomFieldsQueries(AppInstance $instance)
    {
        $queries = [];

        /** @var ObjectAlias\Repository $repository */
        $customFieldTypes = [
            CustomDefTicket::class,
            CustomDefPerson::class,
            CustomDefOrganization::class
        ];

        foreach ($customFieldTypes as $fieldType) {
            $aliasType = ObjectAlias\Aliases::resolveAliasType($fieldType, $this->em);
            $repository  = $this->em->getRepository($aliasType);
            $aliasedObjectsIds = $repository->getAliasedObjectIdsByAppInstance($instance->getId());

            if (count($aliasedObjectsIds)) {
                $queries[] = $this->em
                    ->createQuery(sprintf('UPDATE %s f SET f.title = CONCAT(f.title, :marker) WHERE f.id IN (:ids)', $fieldType))
                    ->setParameter('marker', ' (app removed)')
                    ->setParameter('ids', $aliasedObjectsIds)
                ;
            }

        }

        return $queries;
    }

    private function removeAssets(App $app)
    {
        $entities = [];
        $blobs    = [];

        foreach ($app->getAssets() as $asset) {
            if ($asset->getPath() === '.deskpro/versions/manifest.json.prev') {
                $entities[] = $asset;
                $blobs[]    = $asset->getBlob();
            }
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

}
