<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuerySerializer;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuery;

class DbalCompiledQueryCacher
{
    /**
     * @var CacheAdapterInterface
     */
    private $cache_adapter;

    /**
     * @var DbalCompiledQuerySerializer
     */
    private $serializer;

    public function __construct(CacheAdapterInterface $cache_adapter, DbalCompiledQuerySerializer $serializer)
    {
        $this->cache_adapter = $cache_adapter;
        $this->serializer = $serializer;
    }

    public function fetchQuery($key)
    {
        $key = $this->generateKey($key);

        $serialized = $this->cache_adapter->get($key);

        $compiled_query = $this->serializer->unserialize($serialized);

        return $compiled_query;
    }

    public function saveQuery($key, DbalCompiledQuery $compiled_query)
    {
        $serialized = $this->serializer->serialize($compiled_query);

        $key = $this->generateKey($key);

        $this->cache_adapter->set($key, $serialized);
    }

    /**
     * Prefixes the key from the engine to ensure its unique in the global cache namespace
     *
     * @param $key
     * @return string
     */
    private function generateKey($key)
    {
        $add_unique = sprintf('dbal.term_engine.query.%s', $key);
        return $add_unique;
    }
}
