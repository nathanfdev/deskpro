<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

use Application\DeskPRO\Entity\Blob as BlobEntity;

/**
 * Class Blob.
 */
class Blob implements MessengerModelInterface
{
    /**
     * @var BlobEntity
     */
    private $blob;

    /**
     * @param BlobEntity $blob
     *
     * Blob constructor
     */
    public function __construct(BlobEntity $blob)
    {
        $this->blob = $blob;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'link'      => $this->blob->getDownloadUrl(true),
            'blob_id'   => $this->blob->getId(),
            'blob_auth' => $this->blob->getAuthId(),
        ];
    }
}
