<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Psr\Log\LoggerInterface;

/**
 * Class TicketChecker.
 */
class TicketChecker implements PhpTicketCheckerInterface
{
    /**
     * @var PhpCheck
     */
    private $php_check;

    /**
     * @var TermEngineContext
     */
    private $context;

    /**
     * @var TermEngineExpressionLanguage
     */
    private $expression_language;

    /**
     * @var TermCompilerHelperPool
     */
    private $helper_pool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param PhpCheck                     $php_check
     * @param TermEngineContext            $context
     * @param TermEngineExpressionLanguage $expression_language
     * @param TermCompilerHelperPool       $helper_pool
     * @param LoggerInterface              $logger
     */
    public function __construct(
        PhpCheck                     $php_check,
        TermEngineContext            $context,
        TermEngineExpressionLanguage $expression_language,
        TermCompilerHelperPool       $helper_pool,
        LoggerInterface              $logger
    ) {
        $this->php_check           = clone $php_check; // use a clone
        $this->context             = $context;
        $this->expression_language = $expression_language;
        $this->helper_pool         = $helper_pool;
        $this->logger              = $logger;
    }

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function isTicketMatch(Ticket $ticket)
    {
        $this->php_check->freezeVariableNames();

        $this->logger->debug('TicketChecker: evaluating php check', ['expression' => $this->php_check->getExpression(), 'vars' => $this->php_check->getVariables()]);
        $this->logger->debug('TicketChecker: using ticket', ['ticket_id' => $ticket->getId()]);

        $timer = new SimpleTimer();

        $eval = $this->expression_language->evaluate(
            $this->php_check->getExpression(),
            array_merge(
                $this->php_check->getVariables(),
                [
                    'agent'       => $this->context->getAgent(),
                    'ticket'      => $ticket,
                    'helper_pool' => $this->helper_pool,
                ]
            )
        );

        $eval = (bool) $eval;

        $this->logger->debug('TicketChecker: '.($eval ? 'PASS' : 'FAIL'), ['time' => $timer->getElapsedTime()]);

        return $eval;
    }
}
