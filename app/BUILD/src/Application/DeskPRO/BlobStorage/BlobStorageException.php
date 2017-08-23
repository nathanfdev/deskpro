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
