<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\BlobStorage\BlobStorageException;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\TicketAttachment;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Class TicketAttachmentBlobCheckerListener.
 */
class TicketAttachmentBlobCheckerListener
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobStorage
     * @param LoggerInterface    $logger
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blobStorage, LoggerInterface $logger)
    {
        $this->em          = $em;
        $this->logger      = $logger;
        $this->blobStorage = $blobStorage;
    }

    /**
     * @param TicketAttachment $entity
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function prePersist(TicketAttachment $entity)
    {
        $blob = $entity->getBlob();

        if ($entity->isInline() || !$blob || $blob->isTicketAttachment()) {
            return;
        }

        $this->logger->info(sprintf(
            '[TicketAttachmentBlobCheckerListener] TicketAttachment for the Ticket (#%s) has Blob (#%s) that is not tagged as ticket attachment. Going to recreate tagged Blob.',
            $entity->getTicket() ? $entity->getTicket()->getId().' '.$entity->getTicket()->getTitle() : 'unknown',
            $blob->getId() ? $blob->getId() : $blob->getFilenameSafe()
        ));

        $newBlob = null;

        try {
            $raw_file = $this->blobStorage->copyBlobRecordToString($blob);
            $newBlob  = $this->blobStorage->createBlobRecordFromString(
                $raw_file,
                $blob->getFilename(),
                $blob->getContentType(),
                ['tag' => DeskproBlobStorage::TAG_TICKET_ATTACHMENT]
            );
            $newBlob->setIsTemp(false)->setSourceRef('ticket_attachment.'.$entity->getId());
        } catch (BlobStorageException $ex) {
            $this->logger->error(sprintf(
                '[TicketAttachmentBlobCheckerListener] Catch the BlobStorageException: %s.',
                $ex->getMessage()
            ));
        }

        if ($newBlob) {
            //set old blob as original blob
            $newBlob->setOriginalBlob($blob);
            $this->em->persist($newBlob);

            $entity->setBlob($newBlob);
            $blob->setIsTemp(true)->setSourceRef('ticket_attachment.'.$entity->getId());
            $this->em->persist($blob);
        }
    }
}
