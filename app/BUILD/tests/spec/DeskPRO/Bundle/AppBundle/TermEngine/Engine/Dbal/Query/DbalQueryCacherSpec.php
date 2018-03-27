<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuerySerializer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryCacher
 */
class DbalQueryCacherSpec extends ObjectBehavior
{
    public function let(
        CacheAdapterInterface $adapter,
        DbalQuerySerializer $serializer,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($adapter, $serializer, $logger);
    }

    public function it_will_retrieve_a_cached_query(
        DbalQuery $compiled,
        DbalQuerySerializer $serializer,
        CacheAdapterInterface $adapter
    ) {
        $adapter->get('dbal.term_engine.query.'.'some key from engine')->shouldBeCalled();
        $serializer->unserialize(Argument::any())->willReturn($compiled);

        $this->fetchQuery('some key from engine')->shouldReturn($compiled);
    }

    public function it_will_cache_a_query(
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery $compiled,
        DbalQuerySerializer $serializer,
        CacheAdapterInterface $adapter
    ) {
        $serializer->serialize($compiled)->willReturn($serialized_version = 'serialized_version');

        $this->saveQuery('some key from engine', $compiled);

        $adapter->set('dbal.term_engine.query.'.'some key from engine', $serialized_version)->shouldHaveBeenCalled();
    }
}
