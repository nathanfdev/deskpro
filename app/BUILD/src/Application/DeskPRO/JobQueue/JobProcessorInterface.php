<?php

namespace Application\DeskPRO\JobQueue;

/**
 * Interface JobProcessorInterface.
 */
interface JobProcessorInterface
{
    /**
     * $job['data'] MUST be the payload that the job works with. We pass the whole dbal row in
     * because otherwise the processor can't make its own decisions about logging and job status.
     *
     * @param array $job the job row from the dbal
     */
    public function execute(array $job);

    /**
     * $job['type'] will usually be checked here to determine if its the right type of job for this processor.
     *
     * @param array $job the job row from the dbal
     *
     * @return bool TRUE if this processor can handle the job, FALSE otherwise
     */
    public function canHandle(array $job);
}
