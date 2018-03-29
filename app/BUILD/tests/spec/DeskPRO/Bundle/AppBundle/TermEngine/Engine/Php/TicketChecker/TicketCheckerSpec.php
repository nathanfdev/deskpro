<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TicketChecker
 */
class TicketCheckerSpec extends ObjectBehavior
{
    public function let(
        TermEngineExpressionLanguage $expression_language,
        TermEngineContext $context,
        PhpCheck $check,
        TermCompilerHelperPool $helper_pool,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($check, $context, $expression_language, $helper_pool, $logger);
    }

    public function it_is_the_default_implementation_of_a_ticket_checker()
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerInterface');
    }

    public function it_checks_a_ticket(
        Ticket $ticket,
        TermEngineExpressionLanguage $expression_language,
        TermEngineContext $context,
        PhpCheck $check,
        Person $person,
        TermCompilerHelperPool $helper_pool
    ) {
        $check->getExpression()->willReturn(
            $expression = 'ticket.subject == foo and agent.getId() = ticket.agent_id'
        );
        $check->getVariables()->willReturn(
            [
                'foo' => 'bar',
            ]
        );
        $check->freezeVariableNames()->shouldBeCalled();
        $context->getAgent()->willReturn($person);

        $expression_language->evaluate(
            $expression,
            [
                'foo'         => 'bar',
                'agent'       => $person,
                'ticket'      => $ticket,
                'helper_pool' => $helper_pool,
            ]
        )->willReturn(true);

        $this->isTicketMatch($ticket)->shouldBe(true);
    }
}
