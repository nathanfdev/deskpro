<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PersonListener.
 */
class PersonListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Person[]
     */
    private $updateQueue = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postFlush',
            'onClear',
        ];
    }

    /**
     * @param Person $entity
     */
    public function prePersist(Person $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param Person $entity
     */
    public function preUpdate(Person $entity)
    {
        $this->verifyBrand($entity);
    }

    /**
     * @param Person $entity
     */
    private function verifyBrand(Person $entity)
    {
        if (!count($entity->getBrands())) {
            $this->updateQueue[] = $entity;
        }
    }

    public function onClear()
    {
        $this->updateQueue = [];
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->updateQueue) {
            // person should have at least one brand
            // set a default one
            $em    = $args->getEntityManager();
            $brand = $this->container->get('default_brand_finder')->getDefaultBrand();
            if ($brand) {
                foreach ($this->updateQueue as $entity) {
                    $entity->addBrand($brand);
                    $em->persist($entity);
                }

                $this->updateQueue = [];
                $em->flush();
            }
        }
    }
}
