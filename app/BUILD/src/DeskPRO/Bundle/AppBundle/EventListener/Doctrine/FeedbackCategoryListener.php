<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\FeedbackCategory;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class FeedbackCategoryListener.
 */
class FeedbackCategoryListener
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
     * @param FeedbackCategory $entity
     */
    public function prePersist(FeedbackCategory $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param FeedbackCategory   $entity
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(FeedbackCategory $entity, LifecycleEventArgs $args)
    {
        $this->verifyBrand($entity);

        $em   = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);
    }

    /**
     * @param FeedbackCategory $entity
     */
    private function verifyBrand(FeedbackCategory $entity)
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
