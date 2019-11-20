<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

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
     * @var RouterInterface
     */
    private $urlGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param CreateEntityHelper    $createEntityHelper
     * @param PersonHelper          $personHelper
     * @param BlobAdapter           $blobAdapter
     * @param EntityPersister       $persister
     * @param UrlGeneratorInterface $urlGenerator
     * @param LoggerInterface       $logger
     */
    public function __construct(
        CreateEntityHelper    $createEntityHelper,
        PersonHelper          $personHelper,
        BlobAdapter           $blobAdapter,
        EntityPersister       $persister,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface       $logger
    ) {
        $this->createEntityHelper = $createEntityHelper;
        $this->personHelper       = $personHelper;
        $this->blobAdapter        = $blobAdapter;
        $this->persister          = $persister;
        $this->urlGenerator       = $urlGenerator;
        $this->logger             = $logger;
    }

    /**
     * Persist attachment.
     *
     * @param MapperInterface  $mapper
     * @param Model\Attachment $model
     * @param mixed            $entity
     *
     * @return mixed
     */
    public function createOrUpdateAttachment(MapperInterface $mapper, Model\Attachment $model, $entity)
    {
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
        if ($model->isInline()) {
            $reflection = new \ReflectionClass($attachment);

            if ($reflection->hasMethod('setIsInline')) {
                $attachment->setIsInline(true);
            }
        }

        if (!$entity->getAttachments()->contains($attachment)) {
            $entity->addAttachment($attachment);
        }

        $this->persister->persistAndFlush($attachment, $model);

        return $attachment;
    }

    /**
     * @param Model\Attachment $model
     * @param mixed            $attachment
     * @param string           $content
     *
     * @return string
     */
    public function replaceContent(Model\Attachment $model, $attachment, $content)
    {
        $fn = function ($m) use ($model, $attachment) {
            if ((int) $m[1] !== (int) $model->getOid() && $m[2] === $model->getFileName()) {
                // return as is
                return $m[0];
            }

            /** @var Blob $blob */
            $blob    = $attachment->getBlob();
            $baseUrl = $this->urlGenerator->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

            return '<img src="'.$baseUrl.'file.php/'.$blob->getAuthcode().'/'.urlencode($blob->getFilename()).'" data-blob_id="'.$blob->getId().'" />';
        };

        return preg_replace_callback('#\[attach:(\d+):(.*?)\]#', $fn, $content);
    }
}
