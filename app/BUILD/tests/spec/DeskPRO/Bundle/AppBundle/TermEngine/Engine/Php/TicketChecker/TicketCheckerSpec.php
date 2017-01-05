<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
