<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\BlobStorage\BlobStorageException;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Download;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Class DownloadAttachmentBlobCheckerListener.
 */
class DownloadAttachmentBlobCheckerListener
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
     * @param Download $entity
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function prePersist(Download $entity)
    {
        $blob = $entity->getBlob();

        if (!$blob || $blob->isDownloadAttachment()) {
            return;
        }

        $this->logger->info(sprintf(
            '[DownloadAttachmentBlobCheckerListener] Download (#%s) has Blob (#%s) that is not tagged as download attachment. Going to recreate tagged Blob.',
            $entity->getId(),
            $blob->getId() ? $blob->getId() : $blob->getFilenameSafe()
        ));

        $newBlob = null;

        try {
            $raw_file = $this->blobStorage->copyBlobRecordToString($blob);
            $newBlob  = $this->blobStorage->createBlobRecordFromString(
                $raw_file,
                $blob->getFilename(),
                $blob->getContentType(),
                ['tag' => DeskproBlobStorage::TAG_DOWNLOAD_ATTACHMENT]
            );
            $newBlob->setIsTemp(false)->setSourceRef('publish.download.'.$entity->getId());
        } catch (BlobStorageException $ex) {
            $this->logger->error(sprintf(
                '[DownloadAttachmentBlobCheckerListener] Catch the BlobStorageException: %s.',
                $ex->getMessage()
            ));
        }

        if ($newBlob) {
            $entity->setBlob($newBlob);
            $blob->setIsTemp(true)->setSourceRef('publish.download.'.$entity->getId());
            $this->em->persist($blob);
        }
    }
}
