<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
