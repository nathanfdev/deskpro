<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\BlobStorage;

use Exception;
use Orb\Util\Util;

class BlobStorageException extends \Exception
{
    /** Failed to fetch a blob with a given ID */
    const INVALID_BLOB_ID = 100;

    /** Failed to fetch an adapter with a given ID */
    const INVALID_ADAPTER_ID = 200;

    /** Failed to store new blob data because all adapters failed */
    const FAILED_BLOB_STORE = 300;

    /**#@+ Various write ops */
    const FAILED_RESOURCE_READ        = 1000;
    const FAILED_RESOURCE_WRITE       = 1100;
    const FAILED_RESOURCE_DELETE      = 1200;
    const CUMULATIVE_TIMEOUT_EXCEEDED = 1300;
    /**#@-*/

    /**
     * BlobStorageException constructor.
     *
     * @param string    $message
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct($message, $code, Exception $previous = null)
    {
        if ($previous) {
            $message .= ' -- '.$previous->getMessage().' ['.implode('.', Util::getClassnameParts($previous)).':'.$previous->getCode().']';
        }

        parent::__construct($message, $code, $previous);
    }
}
