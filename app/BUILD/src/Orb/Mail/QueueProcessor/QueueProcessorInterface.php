<?php

/**
 * Orb.
 */

namespace Orb\Mail\QueueProcessor;

/**
 * A queue processor is something that knows how to enqueue a message
 * and later process it.
 */
interface QueueProcessorInterface
{
    const PROCESS_SUCCESS = 1;
    const PROCESS_FAILURE = 2;
    const PROCESS_STOP    = 4;

    /**
     * Start the queue system.
     */
    public function startQueue();

    /**
     * Shutdown the queue system.
     */
    public function shutdownQueue();

    /**
     * Add a message to the queue.
     *
     * @param Orb\Mail\Message $message
     */
    public function addQueuedMessage(\Orb\Mail\Message $message);

    /**
     * Process each member in a queue with the given callback.
     *
     * @param  $callback
     */
    public function processQueue($callback);
}
