<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Incoming\ProcQueue;

use Application\DeskPRO\Entity\EmailSource;

class RedisProcQueue implements ProcQueueInterface
{
    /**
     * @var \Predis\Client
     */
    private $redis_client = null;

    /**
     * @var string
     */
    private $redis_key;

    /**
     * @param \Predis\Client $client
     * @param string         $redis_key
     */
    public function __construct(\Predis\Client $client, $redis_key)
    {
        $this->redis_client = $client;
        $this->redis_key    = $redis_key;
    }

    /**
     * @param EmailSource $source
     */
    public function enqueueNewEmail(EmailSource $source)
    {
        $this->redis_client->rpush($this->redis_key, [json_encode([
            'source_id' => $source->getId(),
        ])]);
    }
}
