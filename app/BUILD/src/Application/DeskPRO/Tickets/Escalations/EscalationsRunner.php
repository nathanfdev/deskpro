<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\Monolog\NullLogger;
use DpSys\LowError\SystemErrorHandler;
use Monolog\Logger;

/**
 * Class EscalationsRunner.
 */
class EscalationsRunner implements \Countable, \IteratorAggregate
{
    /**
     * @var \Application\DeskPRO\Entity\TicketEscalation[]
     */
    private $escalations;

    /**
     * @var EscalationTicketMatcher
     */
    private $matcher;

    /**
     * @var EscalationExecutor
     */
    private $executor;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var int
     */
    private $time_limit = 0;

    /**
     * @var int
     */
    private $batch_size = 100;

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\TicketEscalation[] $escalations
     * @param EscalationTicketMatcher                        $matcher
     * @param EscalationExecutor                             $executor
     * @param int                                            $batch_size
     * @param int                                            $time_limit
     */
    public function __construct(array $escalations, EscalationTicketMatcher $matcher, EscalationExecutor $executor, $batch_size = 1000, $time_limit = 200)
    {
        $this->escalations = $escalations;
        $this->matcher     = $matcher;
        $this->executor    = $executor;
        $this->batch_size  = $batch_size;
        $this->time_limit  = $time_limit;
        $this->logger      = new NullLogger();
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Runs all escalations.
     */
    public function run()
    {
        $this->logger->debug(sprintf('[EscalationsRunner] %d escalations to run -- batch_size(%d) -- time_limit(%d)', count($this->escalations), $this->batch_size, $this->time_limit));
        $ms_start   = microtime(true);
        $time_start = time();

        foreach ($this->escalations as $esc) {
            $time = time();
            if ($time - $time_start > $this->time_limit) {
                $this->logger->info(sprintf('[EscalationsRunner] Hit time limit, breaking early'));
            }

            try {
                $tickets = $this->matcher->getMatches($esc, $this->batch_size);
            } catch (\Exception $e) {
                $this->logger->error('[EscalationsRunner] Error with escalation query: '.$e->getMessage());
                $this->logger->debug(SystemErrorHandler::formatBacktrace($e->getTrace()));
                SystemErrorHandler::logException($e, true, 'escalation_query_'.$esc->id);
                $tickets = [];
            }
            foreach ($tickets as $t) {
                try {
                    $this->executor->applyEscalation($esc, $t);
                } catch (\Exception $e) {
                    $this->logger->error('[EscalationsRunner] Error applying escalation to ticket: '.$e->getMessage());
                    $this->logger->debug(SystemErrorHandler::formatBacktrace($e->getTrace()));
                    SystemErrorHandler::logException($e, true, 'escalation_apply_'.$esc->id);
                }
            }
        }

        $this->logger->debug(sprintf('[EscalationsRunner] Done in %.4fs', microtime(true) - $ms_start));
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->escalations);
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketEscalation[]
     */
    public function getEscalations()
    {
        return $this->escalations;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->escalations);
    }
}
