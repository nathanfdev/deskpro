<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class CommunityTopickStatusCategoryListener.
 */
class CommunityTopicStatusCategoryListener
{
    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param BrandStack $brandStack
     */
    public function __construct(BrandStack $brandStack)
    {
        $this->brandStack = $brandStack;
    }

    /**
     * @param CommunityTopicStatusCategory $entity
     */
    public function prePersist(CommunityTopicStatusCategory $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param CommunityTopicStatusCategory $entity
     * @param LifecycleEventArgs           $args
     */
    public function preUpdate(CommunityTopicStatusCategory $entity, LifecycleEventArgs $args)
    {
        $this->verifyBrand($entity);

        $em   = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);
    }

    /**
     * @param CommunityTopicStatusCategory $entity
     */
    private function verifyBrand(CommunityTopicStatusCategory $entity)
    {
        if (!$entity->getBrand()) {
            $brand = $this->brandStack->getActive()->getBrand();
            if ($brand) {
                $entity->setBrand($brand);
            } else {
                $brand = $this->brandStack->getDefaultBrand();
                if ($brand) {
                    $entity->setBrand($brand);
                }
            }
        }
    }
}
