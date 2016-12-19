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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEngineEvents;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEnginePostCompileEvent;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEnginePreCompileEvent;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PhpTicketCheckerEngine extends PhpEngine
{
    /**
     * @var PhpTicketCheckerEngineCompiler
     */
    private $compiler;

    /**
     * @var TermEngineExpressionLanguage
     */
    private $expression_language;

    /**
     * @var EventDispatcherInterface
     */
    private $event_dispatcher;

    /**
     * @var TermCompilerHelperPool
     */
    private $helper_pool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PhpTicketCheckerEngineCompiler $compiler,
        TermEngineExpressionLanguage $expression_language,
        EventDispatcherInterface $event_dispatcher,
        TermCompilerHelperPool $helper_pool,
        LoggerInterface $logger
    ) {
        $this->compiler            = $compiler;
        $this->expression_language = $expression_language;
        $this->event_dispatcher    = $event_dispatcher;
        $this->helper_pool         = $helper_pool;
        $this->logger              = $logger;
    }

    /**
     * Takes a filter and the context and returns to you an instance of
     * PhpTicketCheckerInterface that satisfies the filters terms.
     *
     * @param TicketFilter      $filter
     * @param TermEngineContext $context
     *
     * @return PhpTicketCheckerInterface
     */
    public function evaluate(TicketFilter $filter, TermEngineContext $context)
    {
        $timer = new SimpleTimer();

        $this->logger->info(
            'START EVALUATE FILTER',
            [
                'filter_id'    => $filter->getId(),
                'filter_title' => $filter->getTitle(),
            ]
        );
        $this->logger->info('dispatching PRE_COMPILE event');
        $pre_compile_timer = new SimpleTimer();
        $event             = new PhpEnginePreCompileEvent($context, $filter);
        $this->event_dispatcher->dispatch(PhpEngineEvents::PRE_COMPILE, $event);
        $this->logger->info(
            'finished PRE_COMPILE event',
            ['time' => $pre_compile_timer->getElapsedTime()]
        );

        $php_check = $this->compiler->compile($filter);

        $this->logger->info('dispatching POST_COMPILE event');
        $post_compile_timer = new SimpleTimer();
        $event              = new PhpEnginePostCompileEvent($context, $filter, $php_check);
        $this->event_dispatcher->dispatch(PhpEngineEvents::POST_COMPILE, $event);
        $this->logger->info(
            'finished POST_COMPILE event',
            ['time' => $post_compile_timer->getElapsedTime()]
        );

        $this->logger->info(
            'END EVALUATE FILTER',
            [
                'filter_id'    => $filter->getId(),
                'filter_title' => $filter->getTitle(),
                'time'         => $timer->getElapsedTime(),
            ]
        );

        return new TicketChecker($php_check, $context, $this->expression_language, $this->helper_pool, $this->logger);
    }
}
