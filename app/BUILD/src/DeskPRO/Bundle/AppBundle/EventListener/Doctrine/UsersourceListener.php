<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\BrandBundle\Brand\DefaultBrandFinder;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class UsersourceListener.
 */
class UsersourceListener
{
    /**
     * @var DefaultBrandFinder
     */
    private $defaultBrandFinder;

    /**
     * Constructor.
     *
     * @param DefaultBrandFinder $defaultBrandFinder
     */
    public function __construct(DefaultBrandFinder $defaultBrandFinder)
    {
        $this->defaultBrandFinder = $defaultBrandFinder;
    }

    /**
     * @param Usersource $entity
     */
    public function prePersist(Usersource $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param Usersource         $entity
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(Usersource $entity, LifecycleEventArgs $args)
    {
        $this->verifyBrand($entity);

        $em   = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);
    }

    /**
     * @param Usersource $entity
     */
    private function verifyBrand(Usersource $entity)
    {
        if (!count($entity->getBrands()) && !$entity->isAllBrands()) {
            // person should have at least one brand
            // set a default one
            $brand = $this->defaultBrandFinder->getDefaultBrand();
            if ($brand) {
                $entity->addBrand($brand);
            }
        }
    }
}
