<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class FeedbackListener.
 */
class FeedbackListener
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
     * @param Feedback $entity
     */
    public function prePersist(Feedback $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param Feedback           $entity
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(Feedback $entity, LifecycleEventArgs $args)
    {
        $this->verifyBrand($entity);

        $em   = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);
    }

    /**
     * @param Feedback $entity
     */
    private function verifyBrand(Feedback $entity)
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
