<?php

/**
 * Orb.
 */

namespace Orb\Mail\QueueProcessor;

/**
 * Stores messages in the filesystem.
 */
class Filesystem implements QueueProcessorInterface
{
    /** @var string */
    protected $filepath;

    public function __construct($path)
    {
        $path = rtrim($path, '/\\');

        if (!is_dir($path) or !is_writable($path)) {
            throw new \InvalidArgumentException('$path is not writable');
        }

        $this->filepath = $path;
    }

    /**
     * Process queues.
     *
     * @return Message
     */
    public function processQueue($callback)
    {
        $files = scandir($this->filepath);

        foreach ($files as $file) {
            if (!($message = unserialize(file_get_contents($this->filepath.DIRECTORY_SEPARATOR.$file)))) {
                throw new \RuntimeException('Failed to read or unserialize message');
            }

            $ret = call_user_func($callback, $message);
            if ($ret & self::PROCESS_SUCCESS) {
                if (!unlink($this->filepath.DIRECTORY_SEPARATOR.$file)) {
                    throw new \RuntimeException('Failed to remove old message file');
                }
            }

            if ($ret & self::PROCESS_STOP) {
                return;
            }
        }
    }

    /**
     * Add a message to the queue.
     *
     * @param Orb\Mail\Message $message
     */
    public function addQueuedMessage(\Orb\Mail\Message $message)
    {
        $name = time().'_'.preg_replace('#[^a-zA-Z0-9]#', '-', $message->getSubject()).'.dat';
        $name = preg_replace('#-{,2}#', '-', $name);

        $path = $this->filepath.DIRECTORY_SEPARATOR.$name;

        if (!file_put_contents($path, serialize($message))) {
            return false;
        }

        return true;
    }

    /**
     * Start the queue system.
     */
    public function startQueue()
    {
    }

    /**
     * Shutdown the queue system.
     */
    public function shutdownQueue()
    {
    }
}
