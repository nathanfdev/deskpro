<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper\PendingQueuer;

use Predis;

class RedisPendingQueuer implements PendingQueuerInterface
{
    /**
     * @var Predis\Client
     */
    private $client;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $data_items = [];

    /**
     * @param Predis\Client $client
     * @param string        $key
     */
    public function __construct(Predis\Client $client, $key)
    {
        $this->client = $client;
        $this->key    = $key;

        $me = $this;
        register_shutdown_function(function () use ($me) {
            try {
                $me->pushAll();
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        });
    }

    /**
     * Pushes all pendning rows to the server.
     */
    public function pushAll()
    {
        foreach ($this->data_items as $d) {
            $this->client->rpush($this->key, [json_encode($d)]);
        }
    }

    /**
     * @param array $source
     */
    public function queueMessageSource(array $source)
    {
        $data = [
            'id' => $source['id'],
        ];

        if (defined('DPC_IS_CLOUD')) {
            $data['dpc_site_id'] = DPC_SITE_ID;
        }

        $this->data_items[] = $data;
    }
}
