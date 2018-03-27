<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Log;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class TermEngineBufferHandler.
 */
class TermEngineBufferHandler extends AbstractProcessingHandler
{
    /**
     * @var array
     */
    private $records = [];

    /**
     * Print log.
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->records[] = $record;
    }
}
