<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class UsersourceListener.
 */
class UsersourceListener
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param EntityManager    $em
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, SettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
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

            $brand = null;

            // get default brand from settings
            $defaultBrandId = $this->settingsResolver->getGlobalSettings()->get('portal.default_brand');
            if ($defaultBrandId) {
                $brand = $this->em->getRepository(Brand::class)->find($defaultBrandId);
            }

            // get first brand as fallback
            if (!$brand) {
                $brand = $this->em->getRepository(Brand::class)->findOneBy([]);
            }

            if ($brand) {
                $entity->addBrand($brand);
            }
        }
    }
}
