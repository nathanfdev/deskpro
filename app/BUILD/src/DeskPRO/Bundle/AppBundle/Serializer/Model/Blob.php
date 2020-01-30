<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Blob as BlobEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Blob.
 */
class Blob
{
    /**
     * Blob content type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $contentType;

    /**
     * True if it's image.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isImage;

    /**
     * Blob identity.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $blobId;

    /**
     * Auth string.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $blobAuth;

    /**
     * Auth identity for this blob.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $blobAuthId;

    /**
     * URL where actual data is.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $downloadUrl;

    /**
     * Name of blob.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $filename;

    /**
     * Human readable filesize (B, Kb, Mb).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $filesizeReadable;

    /**
     * Blob constructor.
     *
     * @param BlobEntity $blob
     * @param SideloadSerializationContext $context
     */
    public function __construct(BlobEntity $blob, SideloadSerializationContext $context)
    {
        $container         = $context->getContainer();
        $this->downloadUrl = $container === null
            ? $blob->getDownloadUrl(true, false)
            : $container->getRouter()
                ->generate(
                    'serve_blob',
                    [
                        'blob_auth_id' => $blob->getAuthId(),
                        'filename'     => $blob->getFilenameSafe(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

        $this->contentType      = $blob->getContentType();
        $this->isImage          = $blob->isImage();
        $this->blobId           = $blob->getId();
        $this->blobAuth         = $blob->getAuthcode();
        $this->blobAuthId       = $blob->getId().'-'.$blob->getAuthcode();
        $this->filename         = $blob->getFilenameSafe();
        $this->filesizeReadable = $blob->getReadableFilesize();
    }
}
