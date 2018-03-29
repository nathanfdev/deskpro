<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Writer;

use DeskPRO\Bundle\AppBundle\Entity\ApiLog;

/**
 * Interface WriterInterface.
 */
interface WriterInterface
{
    /**
     * @param ApiLog $log
     *
     * @return mixed
     */
    public function write(ApiLog $log);
}
