<?php

namespace Application\EmailBundle\SourceMapper\PendingQueuer;

use Predis;

class CloudEmailPendingQueuer implements PendingQueuerInterface
{
    /** @var Predis\Client */
    private $client;

    /** @var string */
    private $redisSet;

    /**
     * @param Predis\Client $client
     * @param string $redisSet the key of the redis ordered set which acts as queue
     * @return CloudEmailPendingQueuer
     * @throws \Exception
     */
    static public function createClient(Predis\Client $client, $redisSet)
    {
        if (empty($redisSet) || !is_string($redisSet)) {
            throw new \Exception("a valid redis key is required to identify the redis set");
        }

        return new CloudEmailPendingQueuer($client, $redisSet);
    }

    /**
     * @param mixed $redisConnection @see https://github.com/nrk/predis/wiki/Connection-Parameters
     * @param string $redisSet the key of the redis ordered set which acts as queue
     * @return CloudEmailPendingQueuer
     * @throws \Exception
     */
    static public function create($redisConnection, $redisSet)
    {
        return CloudEmailPendingQueuer::createClient(
            new Predis\Client($redisConnection),
            $redisSet
        );
    }

    /**
     * CloudEmailPendingQueuer constructor.
     * @param Predis\Client $client
     * @param string $redisSet
     */
    public function __construct( Predis\Client $client, $redisSet)
    {
        $this->client = $client;
        $this->redisSet = $redisSet;
    }

    /**
     * Adds a message to an external queue service.
     *
     * @param array $source
     */
    public function queueMessageSource(array $source)
    {
        $millis = (int) (microtime(true) * 1000);
        $this->client->zadd($this->redisSet, [DPC_SITE_ID => $millis]);
    }
}
