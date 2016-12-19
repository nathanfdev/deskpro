<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineEvent;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineEvents;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\EventListener\DbalQueryManipulatorListener;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class DbalTicketFilterEngine.
 */
class DbalTicketFilterEngine
{
    /**
     * @var DbalTicketFilterEngineCompiler
     */
    private $compiler;

    /**
     * @var DbalQueryManipulatorListener
     */
    private $eventDispatcher;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param DbalTicketFilterEngineCompiler $compiler
     * @param EventDispatcherInterface       $eventDispatcher
     * @param Connection                     $connection
     * @param LoggerInterface                $logger
     */
    public function __construct(
        DbalTicketFilterEngineCompiler $compiler,
        EventDispatcherInterface       $eventDispatcher,
        Connection                     $connection,
        LoggerInterface                $logger
    ) {
        $this->compiler        = $compiler;
        $this->eventDispatcher = $eventDispatcher;
        $this->connection      = $connection;
        $this->logger          = $logger;
    }

    /**
     * @param TicketFilter      $filter
     * @param TermEngineContext $context
     *
     * @return DbalExecutableQuery
     */
    public function evaluate(TicketFilter $filter, TermEngineContext $context)
    {
        $timer = new SimpleTimer();
        $this->logger->info('START EVALUATE FILTER', [
            'filter_id'    => $filter->getId(),
            'filter_title' => $filter->getTitle(),
        ]);

        $compiledQuery = $this->compiler->compile($filter);

        $event = new DbalEngineEvent($compiledQuery, $context);
        $this->eventDispatcher->dispatch(DbalEngineEvents::MANIPULATE_QUERY, $event);

        $this->logger->info('END EVALUATE FILTER', [
            'filter_id'    => $filter->getId(),
            'filter_title' => $filter->getTitle(),
            'time'         => $timer->getElapsedTime(),
        ]);

        $query = new DbalExecutableQuery($compiledQuery, $this->connection, $this->logger);

        // Do we need to apply grouping clauses?
        if (count($context->getGroupBys()) > 0) {
            foreach ($context->getGroupBys() as $group_by) {
                $query->addCountGroup($group_by->getColumn(), $group_by->getSelect());
                if ($group_by->isCustomField()) {
                    $query->addCustomFieldTableJoins($group_by->getCustomFieldId());
                }
                if ($group_by->getOrderBy()) {
                    foreach ($group_by->getOrderBy() as $col => $dir) {
                        $query->addOrderBy($col, $dir);
                    }
                }
            }
        }

        return $query;
    }
}
