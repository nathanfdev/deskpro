<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
