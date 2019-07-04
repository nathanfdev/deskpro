<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class CustomDefCommunityTopicListener.
 */
class CustomDefCommunityTopicListener
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
     * @param CustomDefCommunityTopic $entity
     */
    public function prePersist(CustomDefCommunityTopic $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param CustomDefCommunityTopic $entity
     * @param LifecycleEventArgs      $args
     */
    public function preUpdate(CustomDefCommunityTopic $entity, LifecycleEventArgs $args)
    {
        $this->verifyBrand($entity);

        $em   = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);
    }

    /**
     * @param CustomDefCommunityTopic $entity
     */
    private function verifyBrand(CustomDefCommunityTopic $entity)
    {
        if ($entity->getParent()) {
            $entity->setBrand($entity->getParent()->getBrand());
        }

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
