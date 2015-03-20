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
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 *
 *
 *
 *
 * NOTE: this spec does tons of options testing. this is to target
 *       the functionality in the base AbstractTerm class. Since
 *       all other terms inherit what is being tested here, it is safe
 *       to not repeat these tests in future terms. If you change
 *       the default behaviour of AbstractTerm in your term, then
 *       you should test those changes and make sure the remaining
 *       functionality tested here still works on your new term.
 *
 *
 *
 *
 *
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm
 */
class AgentTermSpec extends ObjectBehavior
{
    function it_is_a_term()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermInterface');
    }

    public function it_defaults_to_is_op()
    {
        $this->getOp()->shouldReturn(TermInterface::OP_IS);
    }

    public function it_lets_you_change_the_op()
    {
        $this->setOp(TermInterface::OP_NOT);

        $this->getOp()->shouldReturn(TermInterface::OP_NOT);
    }

    function it_sets_up_its_own_settings_resolver(OptionsResolver $options_resolver)
    {
        $options_resolver->setDefaults(
            array(
                'agent_ids' => array(),
                'is_active' => true
            )
        )->shouldBeCalled();

        $options_resolver->setAllowedValues(
            array(
                'is_active' => array(true, false)
            )
        )->shouldBeCalled();

        $options_resolver->setAllowedTypes(
            array(
                'agent_ids' => 'array'
            )
        )->shouldBeCalled();

        $this->setDefaultOptions($options_resolver);
    }

    function it_gets_defaults_if_no_options_set()
    {
        $this->getOptions()->shouldReturn(
            array(
                'agent_ids' => array(),
                'is_active' => true
            )
        );
    }

    function it_allows_changing_a_single_option()
    {
        $this->setOption('agent_ids', array(5));

        $this->getOptions()->shouldReturn(
            array(
                'agent_ids' => array(5),
                'is_active' => true
            )
        );
    }

    function it_allows_you_to_get_a_single_resolved_option()
    {
        $this->getOption('agent_ids')->shouldReturn(array());

        $this->setOption('agent_ids', array(6));

        $this->getOption('agent_ids')->shouldReturn(array(6));
    }

    function it_allows_replacing_all_options_with_a_new_set()
    {
        $new_options = array(
            'agent_ids' => array(10),
            'is_active' => false
        );

        $this->replaceOptions($new_options);

        $this->getOptions()->shouldReturn($new_options);
    }

    function it_allows_changing_many_options_at_once()
    {
        $this->setOptions(
            array(
                'agent_ids' => array(10),
                'is_active' => false
            )
        );

        $this->getOptions()->shouldReturn(
            array(
                'agent_ids' => array(10),
                'is_active' => false
            )
        );
    }

    function it_allows_instantiating_with_options()
    {
        $this->beConstructedWith(
            array(
                'agent_ids' => array(10)
            )
        );

        $this->getOptions()->shouldReturn(
            array(
                'agent_ids' => array(10),
                'is_active' => true
            )
        );
    }

    function it_allows_removing_an_option_that_was_set_previously_and_reverts_to_default()
    {
        $this->setOption('agent_ids', array(1101));

        $this->getOptions()->shouldReturn(
            array(
                'agent_ids' => array(1101),
                'is_active' => true
            )
        );

        // now remove it and see defaults again

        $this->removeOption('agent_ids');


        $this->getOptions()->shouldReturn(
            array(
                'agent_ids' => array(),
                'is_active' => true
            )
        );
    }

    function it_can_serialize_itself()
    {
        $serialized = $this->serialize();

        $serialized->shouldBeLike(
            array(
                'op' => TermInterface::OP_IS,
                'options' => array()
            )
        );

        $this->setOp(TermInterface::OP_NOT);
        $this->setOption('agent_ids', array(4, 5, 6));

        $serialized = $this->serialize();

        $serialized->shouldBeLike(
            array(
                'op' => TermInterface::OP_NOT,
                'options' => array(
                    'agent_ids' => array(4, 5, 6)
                )
            )
        );
    }
}
