<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use Application\DeskPRO\Entity\Person;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalQueryManipulator;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalQueryManipulator
 */
class DbalQueryManipulatorSpec extends ObjectBehavior
{
    function let(
        TermEngineExpressionLanguage $expression_language
    )
    {
        $this->beConstructedWith($expression_language);
    }

    function it_will_manipulate_agent_permissions(
        DbalQuery $query,
        TermEngineContext $engine_context
    )
    {
        $this->ensureAgentPermissions($query, $engine_context);
    }

    function it_will_resolve_query_parameters(
        DbalQuery $query,
        TermEngineContext $engine_context,
        TermEngineExpressionLanguage $expression_language,
        Person $person
    )
    {
        $query->getParameters()->willReturn(
            array(
                'one_term' => 'just a string',
                'exp_term' => $exp = new TermEngineExpression('agent.getId()'),
                'deep' => array(
                    'key' => 5,
                    'agent' => $exp
                )
            )
        );

        $engine_context->getAgent()->willReturn($person);
        $person->getId()->willReturn(2);

        $expression_language->evaluate('agent.getId()', array('agent' => $person))->willReturn(2);

        $query->replaceParameter('exp_term', 2)->shouldBeCalled();
        $query->replaceParameter(
            'deep',
            array(
                'key' => 5,
                'agent' => 2
            )
        )->shouldBeCalled();

        $manipulated = $this->resolveParameters($query, $engine_context);
    }
}
