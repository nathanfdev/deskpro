<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\CustomDefFeedback;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class CustomDefFeedbackListener.
 */
class CustomDefFeedbackListener
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
     * @param CustomDefFeedback $entity
     */
    public function prePersist(CustomDefFeedback $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param CustomDefFeedback  $entity
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(CustomDefFeedback $entity, LifecycleEventArgs $args)
    {
        $this->verifyBrand($entity);

        $em   = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);
    }

    /**
     * @param CustomDefFeedback $entity
     */
    private function verifyBrand(CustomDefFeedback $entity)
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
