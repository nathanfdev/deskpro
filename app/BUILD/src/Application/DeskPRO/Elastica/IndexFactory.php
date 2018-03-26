<?php

namespace Application\DeskPRO\Elastica;

use Elastica\Client as EClient;

/**
 * DeskPRO.
 */
class IndexFactory
{
    /**
     * @var \Elastica\Client
     */
    private $client;

    /**
     * @param EClient $client
     */
    public function __construct(EClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $index_name
     *
     * @return \Elastica\Index
     */
    public function getIndex($index_name)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if (defined('DPC_IS_CLOUD') && DPC_IS_CLOUD) {
            return $this->client->getIndex($index_name.'_'.DPC_SITE_ID);
        } elseif (defined('DP_ELASTIC_INDEX')) {
            return $this->client->getIndex(DP_ELASTIC_INDEX);
        } elseif ($DP_ENV && $DP_ENV->getConfig('settings.elastic_index_name')) {
            return $this->client->getIndex($DP_ENV->getConfig('settings.elastic_index_name'));
        } else {
            return $this->client->getIndex($index_name);
        }
    }
}
