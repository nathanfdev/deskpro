<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\AbstractTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\CustomFieldsTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\PersonTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketDateTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketOwnContextTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketSlaTermsHandler;
use Symfony\Component\DependencyInjection\Container;

class MatcherFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var EnvLoader
     */
    private $loader;

    /**
     * @var AbstractTermsHandler[]
     */
    private $termHandlers;

    /**
     * MatcherFactory constructor.
     *
     * @param Container $container
     * @param EnvLoader $loader
     */
    public function __construct(Container $container, EnvLoader $loader)
    {
        $this->container = $container;
        $this->loader    = $loader;
    }

    /**
     * @return AbstractTermsHandler[]
     */
    private function getTermHandlers()
    {
        if ($this->termHandlers !== null) {
            return $this->termHandlers;
        }

        $this->termHandlers = [
            new TicketBasicTermsHandler(),
            new TicketSlaTermsHandler(),
            new TicketDateTermsHandler(),
            new CustomFieldsTermsHandler(
                $this->loader->getTicketFields(),
                [],
                new ChoiceFieldOptionMapper($this->container->get('doctrine.orm.entity_manager')->getRepository(CustomDefTicket::class))
            ),
            new TicketOwnContextTermsHandler($this->container->get('doctrine.dbal.read_search_connection')),
            new PersonTermsHandler($this->container->get('doctrine.orm.entity_manager')->getRepository(Person::class)),
        ];

        return $this->termHandlers;
    }

    /**
     * @return TicketMatcher
     */
    public function createMatcher()
    {
        $resolver = new ValueResolver();

        $matcher = new TicketMatcher(
            $resolver,
            $this->getTermHandlers()
        );

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

        $matcher = new TicketSqlMatcher(
            $resolver,
            $this->getTermHandlers(),
            $this->container->get('doctrine.dbal.read_search_connection'),
            TicketSqlMatcher::ACTIVE,
            $this->loader->getTicketFields()
        );

        return $matcher;
    }
}
