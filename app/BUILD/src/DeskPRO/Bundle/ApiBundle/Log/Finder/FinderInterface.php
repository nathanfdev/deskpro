<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Finder;

use DeskPRO\Bundle\AppBundle\Entity\ApiLog;

/**
 * Interface FinderInterface.
 */
interface FinderInterface
{
    /**
     * @param $request_id
     *
     * @return ApiLog
     */
    public function find($request_id);
}
