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
