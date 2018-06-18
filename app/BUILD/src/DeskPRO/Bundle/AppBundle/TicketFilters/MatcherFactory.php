<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\CustomFieldsTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\ElasticTermHandlerInterface;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\PersonTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\SqlTermHandlerInterface;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketDateTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketOwnContextTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketSlaTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\ValueTermHandlerInterface;
use Psr\Log\LoggerInterface;
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
     * @var array
     */
    private $termHandlers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MatcherFactory constructor.
     *
     * @param Container       $container
     * @param EnvLoader       $loader
     * @param LoggerInterface $logger
     */
    public function __construct(Container $container, EnvLoader $loader, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->loader    = $loader;
        $this->logger    = $logger;
    }

    /**
     * @return ValueTermHandlerInterface[]|SqlTermHandlerInterface[]
     */
    private function getTermHandlers($ofType)
    {
        if ($this->termHandlers === null) {
            $this->termHandlers = [
                new TicketBasicTermsHandler($this->container->get('deskpro.search_manager.elasticsearch')),
                new TicketSlaTermsHandler(),
                new TicketDateTermsHandler(),
                new CustomFieldsTermsHandler(
                    $this->loader->getCustomFieldsSet(),
                    new ChoiceFieldOptionMapper(
                        $this->loader->getCustomFieldsSet(),
                        $this->container->get('doctrine.orm.entity_manager')->getRepository(CustomDefTicket::class)
                    )
                ),
                new TicketOwnContextTermsHandler($this->container->get('doctrine.dbal.read_search_connection')),
                new PersonTermsHandler($this->container->get('doctrine.orm.entity_manager')->getRepository(Person::class)),
            ];
        }

        return array_filter($this->termHandlers, function ($h) use ($ofType) {
            return $h instanceof $ofType;
        });
    }

    /**
     * @return TicketMatcher
     */
    public function createMatcher()
    {
        $resolver = new ValueResolver();
        $matcher  = new TicketMatcher(
            $resolver,
            $this->getTermHandlers(ValueTermHandlerInterface::class)
        );

        if ($this->logger) {
            $matcher->setLogger($this->logger);
        }

        return $matcher;
    }

    /**
     * @throws \Exception
     *
     * @return TicketSqlMatcher
     */
    public function createSqlMatcher($activeOnly = true)
    {
        $resolver = new ValueResolver();
        $matcher  = new TicketSqlMatcher(
            $resolver,
            $this->getTermHandlers(SqlTermHandlerInterface::class),
            $this->container->get('doctrine.dbal.read_search_connection'),
            $activeOnly ? TicketSqlMatcher::ACTIVE : TicketSqlMatcher::ALL,
            $this->loader->getCustomFieldsSet(),
            new ChoiceFieldOptionMapper(
                $this->loader->getCustomFieldsSet(),
                $this->container->get('doctrine.orm.entity_manager')->getRepository(CustomDefTicket::class)
            )
        );

        if ($this->logger) {
            $matcher->setLogger($this->logger);
        }

        return $matcher;
    }

    /**
     * @return ElasticMatcher
     */
    public function createElasticMatcher()
    {
        $resolver = new ValueResolver();
        $matcher  = new ElasticMatcher(
            $resolver,
            $this->container->get('fos_elastica.index.deskpro.ticket'),
            $this->getTermHandlers(ElasticTermHandlerInterface::class),
            $this->loader->getCustomFieldsSet(),
            new ChoiceFieldOptionMapper(
                $this->loader->getCustomFieldsSet(),
                $this->container->get('doctrine.orm.entity_manager')->getRepository(CustomDefTicket::class)
            )
        );

        if ($this->logger) {
            $matcher->setLogger($this->logger);
        }

        return $matcher;
    }
}
