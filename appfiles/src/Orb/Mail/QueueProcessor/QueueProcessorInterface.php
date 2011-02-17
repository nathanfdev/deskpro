<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Mail\QueueProcessor;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A queue processor is something that knows how to enqueue a message
 * and later process it.
 */
interface QueueProcessorInterface
{
	const PROCESS_SUCCESS = 0;
	const PROCESS_FAILURE = 1;
	const PROCESS_STOP = 2;

	/**
	 * Start the queue system
	 */
	public function startQueue();

	/**
	 * Shutdown the queue system
	 */
	public function shutdownQueue();

	/**
	 * Add a message to the queue
	 *
	 * @param Orb\Mail\Message $message
	 */
	public function addQueuedMessage(\Orb\Mail\Message $message);

	/**
	 * Process each member in a queue with the given callback
	 *
	 * @param  $callback
	 * @return void
	 */
	public function processQueue($callback);
}