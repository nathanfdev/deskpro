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
        $downloadUrl = $this->blob->getDownloadUrl(true);

        return [
            'link'  => $downloadUrl,
            'blob'  => [
                'id'                => $this->blob->getId(),
                'auth_id'           => $this->blob->getAuthId(),
                'auth'              => $this->blob->getAuthcode(),
                'filename'          => $this->blob->getFilename(),
                'filesize_readable' => $this->blob->getFilesize(),
                'download_url'      => $downloadUrl,
                'is_image'          => $this->blob->isImage(),
            ],
        ];
    }
}
