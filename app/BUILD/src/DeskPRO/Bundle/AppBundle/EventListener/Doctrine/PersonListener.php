<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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
     * @param Person             $entity
     * @param PreUpdateEventArgs $eventArgs
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function preUpdate(Person $entity, PreUpdateEventArgs $eventArgs)
    {
        $this->verifyBrand($entity);

        if ($eventArgs->hasChangedField('password')) {
            /** @var \Application\DeskPRO\DBAL\Connection $db */
            /** @var \Doctrine\ORM\EntityManager $em */
            $db = $this->container->getDb();
            $em = $this->container->getEm();

            $db->delete('sessions', ['person_id' => $entity->getId()]);
            $db->delete('sess_data', ['person_id' => $entity->getId()]);

            /** @var \Application\DeskPRO\Entity\ApiToken $token */
            $token = $em
                ->getRepository('DeskPRO:ApiToken')
                ->getTokenForPerson($entity);
            if ($token) {
                $token->regenerateToken();
                $em->persist($token);
            }
        }
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
