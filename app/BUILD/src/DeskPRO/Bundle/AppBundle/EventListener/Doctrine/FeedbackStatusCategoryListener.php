<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\FeedbackStatusCategory;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class FeedbackStatusCategoryListener.
 */
class FeedbackStatusCategoryListener
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
     * @param FeedbackStatusCategory $entity
     */
    public function prePersist(FeedbackStatusCategory $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param FeedbackStatusCategory $entity
     * @param LifecycleEventArgs     $args
     */
    public function preUpdate(FeedbackStatusCategory $entity, LifecycleEventArgs $args)
    {
        $this->verifyBrand($entity);

        $em   = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);
    }

    /**
     * @param FeedbackStatusCategory $entity
     */
    private function verifyBrand(FeedbackStatusCategory $entity)
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
