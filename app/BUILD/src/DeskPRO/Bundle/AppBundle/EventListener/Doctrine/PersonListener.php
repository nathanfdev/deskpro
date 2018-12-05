<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\BrandBundle\Brand\DefaultBrandFinder;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class PersonListener.
 */
class PersonListener
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
     * @param Person $entity
     */
    public function prePersist(Person $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param Person             $entity
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(Person $entity, LifecycleEventArgs $args)
    {
        $this->verifyBrand($entity);

        $em   = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);
    }

    /**
     * @param Person $entity
     */
    private function verifyBrand(Person $entity)
    {
        if (!count($entity->getBrands())) {
            // person should have at least one brand
            // set a default one
            $brand = $this->defaultBrandFinder->getDefaultBrand();
            if ($brand) {
                $entity->addBrand($brand);
            }
        }
    }
}
