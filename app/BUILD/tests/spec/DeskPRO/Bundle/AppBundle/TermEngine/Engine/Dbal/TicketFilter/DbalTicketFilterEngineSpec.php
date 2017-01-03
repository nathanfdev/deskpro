<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineEvents;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngineCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use Doctrine\DBAL\Connection;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine
 */
class DbalTicketFilterEngineSpec extends ObjectBehavior
{
    public function let(
        DbalTicketFilterEngineCompiler $compiler,
        EventDispatcher $event_dispatcher,
        Connection $connection,
        Logger $logger
    ) {
        $this->beConstructedWith($compiler, $event_dispatcher, $connection, $logger);
    }

    public function it_creates_an_executable_query_for_a_filter_using_given_context(
        TicketFilter $filter,
        TermEngineContext $context,
        DbalTicketFilterEngineCompiler $compiler,
        EventDispatcher $event_dispatcher,
        DbalQuery $compiled_query
    ) {
        $filter->getId()->willReturn(1);
        $filter->getTitle()->willReturn('title');
        $compiler->compile($filter)->willReturn($compiled_query);

        $event_dispatcher->dispatch(
            DbalEngineEvents::MANIPULATE_QUERY,
            Argument::any()
        )->shouldBeCalled();

        $executable_query = $this->evaluate($filter, $context);

        $executable_query->shouldBeAnInstanceOf(
            'DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery'
        );
    }
}
