<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryCacher;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler\DbalTicketFilterCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngineCompiler
 */
class DbalTicketFilterEngineCompilerSpec extends ObjectBehavior
{
    public function let(
        DbalTicketFilterCompiler $compiler,
        DbalQueryCacher $query_cache,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($compiler, $query_cache, $logger);
    }

    public function it_compiles_a_filter_if_it_is_not_cached(
        TicketFilter $filter,
        \DateTime $filter_updated,
        TermInterface $filter_term,
        DbalQuery $compiled,
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler\DbalTicketFilterCompiler $compiler,
        DbalQueryCacher $query_cache
    ) {
        $filter->getId()->willReturn(2);
        $filter->getTitle()->willReturn('title');
        $filter->getDateUpdated()->willReturn($filter_updated);
        $filter->getTerm()->willReturn($filter_term);
        $filter_updated->getTimestamp()->willReturn(123456789011);

        // no cached version
        $query_cache->fetchQuery('2.123456789011')->willReturn(null);

        // it should save to the cache
        $query_cache->saveQuery('2.123456789011', $compiled)->shouldBeCalled();

        // so it compiles
        $compiler->compile($filter_term)->willReturn($compiled);

        $this->compile($filter)->shouldReturn($compiled);
    }

    public function it_returns_the_cached_version_of_the_compiled_query_if_exists(
        TicketFilter $filter,
        \DateTime $filter_updated,
        TermInterface $filter_term,
        DbalQuery $compiled,
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler\DbalTicketFilterCompiler $compiler,
        DbalQueryCacher $query_cache
    ) {
        $filter->getId()->willReturn(2);
        $filter->getTitle()->willReturn('title');
        $filter->getDateUpdated()->willReturn($filter_updated);
        $filter_updated->getTimestamp()->willReturn(123456789011);

        // found it! cached version
        $query_cache->fetchQuery('2.123456789011')->willReturn($compiled);

        // so it will not compile
        $compiler->compile($filter_term)->shouldNotBeCalled();

        $this->compile($filter)->shouldReturn($compiled);
    }
}
