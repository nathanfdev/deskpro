<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\TicketAttachment;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TicketAttachmentListener.
 */
class TicketAttachmentListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Blob[]
     */
    private $blobsToRemove = [];

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
            'postRemove',
            'postFlush',
            'onClear',
        ];
    }

    /**
     * @internal
     *
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof TicketAttachment || !$entity->getBlob()) {
            return;
        }

        $blob           = $entity->getBlob();
        $originalBlob   = $blob->getOriginalBlob();
        $blobRepository = $args->getEntityManager()->getRepository(Blob::class);
        $blobs          = $blobRepository->findBy(['original_blob' => $originalBlob ?: $blob]);

        $this->blobsToRemove[$blob->getId()] = $blob;
        if ($originalBlob) {
            $this->blobsToRemove[$originalBlob->getId()] = $originalBlob;
        }

        foreach ($blobs as $blob) {
            $this->blobsToRemove[$blob->getId()] = $blob;
        }
    }

    /**
     * @internal
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->blobsToRemove) {
            $connection = $args->getEntityManager()->getConnection();
            $connection->executeUpdate(
                'UPDATE blobs SET is_temp = 1 WHERE id IN (?)',
                [array_keys($this->blobsToRemove)],
                [Connection::PARAM_INT_ARRAY]
            );

            $this->blobsToRemove = [];
        }
    }

    /**
     * @internal
     */
    public function onClear()
    {
        $this->blobsToRemove = [];
    }
}
