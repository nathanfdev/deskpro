<?php

/**
 * DeskPRO.
 *
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\FetcherStorage;

use Orb\Log\Loggable;

interface FetcherStorageInterface extends Loggable
{
    /**
     * @return mixed
     */
    public function getStorage();

    /**
     * @void
     */
    public function closeStorage();
}
