<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\BlobStorage\StorageAdapter;

use Application\DeskPRO\BlobStorage\Blob;

interface ReadStreamInterface
{
    /**
     * @return resource
     */
    public function getBlobReadStream(Blob $blob);
}
