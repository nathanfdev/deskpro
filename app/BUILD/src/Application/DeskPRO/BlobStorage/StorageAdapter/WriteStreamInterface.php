<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\BlobStorage\StorageAdapter;

use Application\DeskPRO\BlobStorage\Blob;

interface WriteStreamInterface
{
    /**
     * @return resource
     */
    public function getBlobWriteStream(Blob $blob);
}
