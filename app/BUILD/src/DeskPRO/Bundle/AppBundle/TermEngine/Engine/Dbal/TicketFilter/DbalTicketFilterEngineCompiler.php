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
namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler\DbalCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryCacher;
use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Psr\Log\LoggerInterface;

/**
 * This is the compiler that the DbalTicketFilterEngine uses. It is a thin wrapper
 * around the DbalTicketFilterCompiler that adds caching, and is aware of Filter
 * entities instead of just TermInterface's.
 */
class DbalTicketFilterEngineCompiler
{
    /**
     * @var DbalCompiler
     */
    protected $compiler;

    /**
     * @var DbalQueryCacher
     */
    protected $cacher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(DbalCompiler $compiler, DbalQueryCacher $cacher, LoggerInterface $logger)
    {
        $this->compiler = $compiler;
        $this->cacher   = $cacher;
        $this->logger   = $logger;
    }

    /**
     * @param TicketFilter $filter
     *
     * @return DbalQuery|mixed
     */
    public function compile(TicketFilter $filter)
    {
        $this->logger->info('START FILTER COMPILE', array(
            'filter_id'    => $filter->getId(),
            'filter_title' => $filter->getTitle(),
        ));

        $timer = new SimpleTimer();

        $key = $this->generateKey($filter);

        $this->logger->debug('Checking DbalQueryCache', array('key' => $key));

        if ($compiled_query = $this->cacher->fetchQuery($key)) {
            $this->logger->info('Cache hit, exiting compiler', array('time' => $timer->getElapsedTime()));

            return $compiled_query;
        }

        $this->logger->debug('Cache miss, compiling');

        $compiled_query = $this->compiler->compile($filter->getTerm());

        // cache it for future calls to retrieve
        $this->cacher->saveQuery($key, $compiled_query);
        $this->logger->debug('Saved compiled query to the cache store', array('key' => $key));

        $this->logger->info('END FILTER COMPILE', array('time' => $timer->getElapsedTime()));

        return $compiled_query;
    }

    protected function generateKey(TicketFilter $filter)
    {
        return sprintf('%s.%s', $filter->getId(), $filter->getDateUpdated()->getTimestamp());
    }
}
