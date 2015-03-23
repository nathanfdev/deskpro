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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeamTerm;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeamTerm
 */
class AgentTeamTermSpec extends ObjectBehavior
{
    function it_is_a_term()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermInterface');
    }

    function it_has_default_op_is()
    {
        $this->getOp()->shouldBe(TermInterface::OP_IS);
    }

    function it_allows_op_change()
    {
        $this->setOp(TermInterface::OP_NOT);

        $this->getOp()->shouldBe(TermInterface::OP_NOT);
    }

    function it_defines_its_options(
        OptionsResolver $options_resolver
    )
    {
        $options_resolver->setDefaults(
            array(
                'agent_team_ids' => array()
            )
        )->shouldBeCalled();

        $options_resolver->setAllowedTypes(
            array(
                'agent_team_ids' => 'array'
            )
        )->shouldBeCalled();

        $this->setDefaultOptions($options_resolver);
    }
}
