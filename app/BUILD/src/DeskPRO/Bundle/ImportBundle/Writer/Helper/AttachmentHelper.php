<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AttachmentHelper.
 */
class AttachmentHelper
{
    /**
     * @var CreateEntityHelper
     */
    private $createEntityHelper;

    /**
     * @var PersonHelper
     */
    private $personHelper;

    /**
     * @var BlobAdapter
     */
    private $blobAdapter;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param CreateEntityHelper $createEntityHelper
     * @param PersonHelper       $personHelper
     * @param BlobAdapter        $blobAdapter
     * @param EntityPersister    $persister
     * @param LoggerInterface    $logger
     */
    public function __construct(
        CreateEntityHelper $createEntityHelper,
        PersonHelper       $personHelper,
        BlobAdapter        $blobAdapter,
        EntityPersister    $persister,
        LoggerInterface    $logger
    ) {
        $this->createEntityHelper = $createEntityHelper;
        $this->personHelper       = $personHelper;
        $this->blobAdapter        = $blobAdapter;
        $this->persister          = $persister;
        $this->logger             = $logger;
    }

    /**
     * Persist attachment.
     *
     * @param MapperInterface  $mapper
     * @param Model\Attachment $model
     * @param mixed            $entity
     */
    public function createOrUpdateAttachment(MapperInterface $mapper, Model\Attachment $model, $entity)
    {
        /** @var Entity\ArticleAttachment $attachment */
        $attachment = $this->createEntityHelper->findOrCreateEntity($mapper, $model);

        // try to create a blob
        // if blob was not created then skip the attachment
        $blob = $this->blobAdapter->createByBlob($model, false);
        if (!$blob) {
            $this->logger->warning('Blob not found, skipping.');

            return;
        }

        $attachment->setBlob($blob);
        if ($model->getPerson()) {
            $attachment->setPerson($this->personHelper->findOrCreatePerson($model->getPerson()));
        } else {
            $attachment->setPerson(null);
        }

        if (!$entity->getAttachments()->contains($attachment)) {
            $entity->addAttachment($attachment);
        }

        $this->persister->persistAndFlush($attachment, $model);
    }
}
