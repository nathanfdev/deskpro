<?php

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
