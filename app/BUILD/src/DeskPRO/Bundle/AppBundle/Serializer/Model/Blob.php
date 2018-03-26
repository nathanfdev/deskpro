<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Blob as BlobEntity;
use JMS\Serializer\Annotation as JMS;

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
     */
    public function __construct(BlobEntity $blob)
    {
        $this->contentType      = $blob->getContentType();
        $this->isImage          = $blob->isImage();
        $this->blobId           = $blob->getId();
        $this->blobAuth         = $blob->getAuthcode();
        $this->blobAuthId       = $blob->getId().'-'.$blob->getAuthcode();
        $this->downloadUrl      = $blob->getDownloadUrl(true, false);
        $this->filename         = $blob->getFilenameSafe();
        $this->filesizeReadable = $blob->getReadableFilesize();
    }
}
