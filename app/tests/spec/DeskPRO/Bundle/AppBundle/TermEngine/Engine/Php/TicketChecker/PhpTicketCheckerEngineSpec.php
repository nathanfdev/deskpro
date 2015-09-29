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
namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEngineEvents;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerEngineCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerEngine
 */
class PhpTicketCheckerEngineSpec extends ObjectBehavior
{
    public function it_evals_the_compiler_result_and_returns_the_compiled_checker(
        PhpTicketCheckerEngineCompiler $compiler,
        TermEngineExpressionLanguage $expression_language,
        TicketFilter $filter,
        TermEngineContext $context,
        PhpCheck $php_check,
        EventDispatcherInterface $event_dispatcher,
        TermCompilerHelperPool $helper_pool,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($compiler, $expression_language, $event_dispatcher, $helper_pool, $logger);

        $filter->getTitle()->willReturn('title');
        $filter->getId()->willReturn(1);
        $event_dispatcher->dispatch(
            PhpEngineEvents::PRE_COMPILE,
            Argument::type('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEnginePreCompileEvent')
        )->shouldBeCalled();
        $compiler->compile($filter)->willReturn($php_check);
        $event_dispatcher->dispatch(
            PhpEngineEvents::POST_COMPILE,
            Argument::type('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEnginePostCompileEvent')
        )->shouldBeCalled();

        $this->evaluate($filter, $context)->shouldBeAnInstanceOf(
            'DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TicketChecker'
        );
    }
}
