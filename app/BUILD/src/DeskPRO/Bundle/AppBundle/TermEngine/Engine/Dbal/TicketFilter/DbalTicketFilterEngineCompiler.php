<?php

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

    /**
     * Constructor.
     *
     * @param DbalCompiler    $compiler
     * @param DbalQueryCacher $cacher
     * @param LoggerInterface $logger
     */
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
        $this->logger->info('START FILTER COMPILE', [
            'filter_id'    => $filter->getId(),
            'filter_title' => $filter->getTitle(),
        ]);

        $timer = new SimpleTimer();
        $key   = $this->generateKey($filter);

        $this->logger->debug('Checking DbalQueryCache', ['key' => $key]);

        $compiledQuery = $this->cacher->fetchQuery($key);
        if ($compiledQuery) {
            $this->logger->info('Cache hit, exiting compiler', ['time' => $timer->getElapsedTime()]);

            return $compiledQuery;
        }

        $this->logger->debug('Cache miss, compiling');
        $compiledQuery = $this->compiler->compile($filter->getTerm());

        // cache it for future calls to retrieve
        $this->cacher->saveQuery($key, $compiledQuery);
        $this->logger->debug('Saved compiled query to the cache store', ['key' => $key]);
        $this->logger->info('END FILTER COMPILE', ['time' => $timer->getElapsedTime()]);

        return $compiledQuery;
    }

    /**
     * @param TicketFilter $filter
     *
     * @return string
     */
    protected function generateKey(TicketFilter $filter)
    {
        return sprintf('%s.%s', $filter->getId(), $filter->getDateUpdated()->getTimestamp());
    }
}
