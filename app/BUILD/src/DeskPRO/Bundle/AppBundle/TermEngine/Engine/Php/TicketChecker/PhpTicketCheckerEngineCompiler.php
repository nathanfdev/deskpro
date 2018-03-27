<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use DeskPRO\Bundle\AppBundle\Entity\FilterInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheckCacher;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\Compiler\PhpTicketCheckerCompiler;
use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Psr\Log\LoggerInterface;

class PhpTicketCheckerEngineCompiler
{
    /**
     * @var PhpTicketCheckerCompiler
     */
    private $compiler;

    /**
     * @var PhpCheckCacher
     */
    private $cacher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(PhpTicketCheckerCompiler $compiler, PhpCheckCacher $cacher, LoggerInterface $logger)
    {
        $this->compiler = $compiler;
        $this->cacher   = $cacher;
        $this->logger   = $logger;
    }

    public function compile(FilterInterface $filter)
    {
        $this->logger->info(
            'START FILTER COMPILE',
            [
                'filter_id'    => $filter->getId(),
                'filter_title' => $filter->getTitle(),
            ]
        );

        $timer = new SimpleTimer();

        $key = $this->generateKey($filter);

        $this->logger->debug('Checking PhpCheckCache', ['key' => $key]);

        if ($compiled_query = $this->cacher->fetchCheck($key)) {
            $this->logger->info('Cache hit, exiting compiler', ['time' => $timer->getElapsedTime()]);

            return $compiled_query;
        }

        $this->logger->debug('Cache miss, compiling');

        $compiled_query = $this->compiler->compile($filter->getTerm());

        // cache it for future calls to retrieve
        $this->cacher->saveCheck($key, $compiled_query);

        $this->logger->debug('Saved compiled query to the cache store', ['key' => $key]);

        $this->logger->info('END FILTER COMPILE', ['time' => $timer->getElapsedTime()]);

        return $compiled_query;
    }

    protected function generateKey(FilterInterface $filter)
    {
        return sprintf('%s.%s', $filter->getId(), $filter->getDateUpdated()->getTimestamp());
    }
}
