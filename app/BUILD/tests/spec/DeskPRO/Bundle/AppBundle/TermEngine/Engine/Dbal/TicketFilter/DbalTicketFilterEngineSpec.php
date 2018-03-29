<?php

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
