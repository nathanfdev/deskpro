<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketDateTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketSlaTermsHandler;
use Symfony\Component\DependencyInjection\Container;

class MatcherFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * MatcherFactory constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return TicketMatcher
     */
    public function createMatcher()
    {
        $resolver = new ValueResolver();

        $matcher = new TicketMatcher($resolver, [
            new TicketBasicTermsHandler(),
            new TicketSlaTermsHandler(),
            new TicketDateTermsHandler(),
        ]);

        return $matcher;
    }

    /**
     * @throws \Exception
     *
     * @return TicketSqlMatcher
     */
    public function createSqlMatcher()
    {
        $resolver = new ValueResolver();

        $matcher = new TicketSqlMatcher($resolver, [
            new TicketBasicTermsHandler(),
            new TicketSlaTermsHandler(),
            new TicketDateTermsHandler(),
        ], $this->container->get('doctrine.dbal.read_search_connection'), TicketSqlMatcher::ACTIVE);

        return $matcher;
    }
}
