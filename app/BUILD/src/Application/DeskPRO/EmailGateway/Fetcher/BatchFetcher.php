<?php

namespace Application\DeskPRO\EmailGateway\Fetcher;

interface BatchFetcher
{
    /**
     * @param string $object_type
     * @param int    $limit
     *
     * @return \Application\DeskPRO\Entity\EmailSource[]
     */
    public function readBatch($object_type = 'ticket', $limit = 10);
}
