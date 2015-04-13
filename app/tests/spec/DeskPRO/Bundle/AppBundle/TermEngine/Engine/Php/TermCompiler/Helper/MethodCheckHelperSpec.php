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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper\MethodCheckHelper;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper\MethodCheckHelper
 */
class MethodCheckHelperSpec extends ObjectBehavior
{
    function it_is_a_helper()
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface');
        $this->getId()->shouldBe('method_check');
    }


    //
    // CONTAINS
    //

    function it_makes_a_check_with_a_method_name_and_an_array_of_inputs()
    {
        $php_check = $this->checkContains('$ticket->getAgentId()', TermInterface::OP_IS, array(1, 2));

        $php_check->getCheckCode()->shouldBeLike(
            '$check = in_array($ticket->getAgentId(), \Orb\Util\Arrays::flatten(array(1,2)));'
        );
    }

    function it_makes_a_NOT_check_with_a_method_name_and_an_array_of_inputs()
    {
        $php_check = $this->checkContains('$ticket->getAgentId()', TermInterface::OP_NOT, array(1, 2));

        $php_check->getCheckCode()->shouldBeLike(
            '$check = !in_array($ticket->getAgentId(), \Orb\Util\Arrays::flatten(array(1,2)));'
        );
    }

    function it_works_with_String_inputs()
    {
        $php_check = $this->checkContains('$ticket->getAgentId()', TermInterface::OP_IS, array(1, 'homer'));

        $php_check->getCheckCode()->shouldBeLike(
            '$check = in_array($ticket->getAgentId(), \Orb\Util\Arrays::flatten(array(1,\'homer\')));'
        );
    }

    function it_works_with_Expression_inputs()
    {
        $php_check = $this->checkContains(
            '$ticket->getAgentId()',
            TermInterface::OP_IS,
            array(1, 'homer', new TermEngineExpression('agent.getId()'))
        );

        $php_check->getCheckCode()->shouldBeLike(
            '$check = in_array($ticket->getAgentId(), \Orb\Util\Arrays::flatten(array(1,\'homer\',$this->evaluateExpression(\'agent.getId()\'))));'
        );
    }

    function it_optionally_allows_a_second_input_an_expression_to_also_check()
    {
        $php_check = $this->checkContains(
            '$ticket->getAgentId()',
            TermInterface::OP_IS,
            array(1, 'homer', new TermEngineExpression('agent.getTeamIds()'))
        );

        $php_check->getCheckCode()->shouldBeLike(
            '$check = in_array($ticket->getAgentId(), \Orb\Util\Arrays::flatten(array(1,\'homer\',$this->evaluateExpression(\'agent.getTeamIds()\'))));'
        );
    }


    //
    // EQUALITY
    //

    function it_checks_if_equal_by_default()
    {
        $php_check = $this->checkEquality(
            '$ticket->getPersonEmailAddress()',
            TermInterface::OP_IS,
            'chris.tickner@gmail.com'
        );

        $php_check->getCheckCode()->shouldBeLike(
            '$check = ($ticket->getPersonEmailAddress() == \'chris.tickner@gmail.com\');'
        );
    }

    function it_checks_if_identical_with_bool_flag()
    {
        $php_check = $this->checkEquality(
            '$ticket->getPersonEmailAddress()',
            TermInterface::OP_IS,
            'chris.tickner@gmail.com',
            true
        );

        $php_check->getCheckCode()->shouldBeLike(
            '$check = ($ticket->getPersonEmailAddress() === \'chris.tickner@gmail.com\');'
        );
    }

    function it_checks_if_not_equal()
    {
        $php_check = $this->checkEquality(
            '$ticket->getPersonEmailAddress()',
            TermInterface::OP_NOT,
            'chris.tickner@gmail.com'
        );

        $php_check->getCheckCode()->shouldBeLike(
            '$check = ($ticket->getPersonEmailAddress() != \'chris.tickner@gmail.com\');'
        );
    }

    function it_checks_equality_of_expressions_too()
    {
        $php_check = $this->checkEquality(
            '$ticket->getAgentId()',
            TermInterface::OP_IS,
            new TermEngineExpression('agent.getId()')
        );

        $php_check->getCheckCode()->shouldBeLike(
            '$check = ($ticket->getAgentId() == $this->evaluateExpression(\'agent.getId()\'));'
        );
    }
}
