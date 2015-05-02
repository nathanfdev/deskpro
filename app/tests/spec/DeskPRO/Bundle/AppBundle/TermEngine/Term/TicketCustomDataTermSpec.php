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
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomDataTerm;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomDataTerm
 */
class TicketCustomDataTermSpec extends ObjectBehavior
{
    function let()
    {
        $this->setOption('field_id', 1);
    }

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

    function it_defines_its_options()
    {
        $resolver = $this->getOptionsResolver();
        $resolver->isDefined('input')->shouldBe(true);
        $resolver->isDefined('values')->shouldBe(true);
        $resolver->isDefined('field_id')->shouldBe(true);
        $resolver->isRequired('field_id')->shouldBe(true);
    }

    function it_gets_defaults_if_no_options_set()
    {
        $this->getOptions()->shouldReturn(
            array(
                'input' => null,
                'values' => array(),
                'field_id' => 1
            )
        );
    }

    function it_allows_changing_a_single_option()
    {
        $this->setOption('values', array(5));

        $this->getOptions()->shouldReturn(
            array(
                'input' => null,
                'values' => array(5),
                'field_id' => 1
            )
        );
    }

    function it_allows_you_to_get_a_single_resolved_option()
    {
        $this->getOption('values')->shouldReturn(array());

        $this->setOption('values', array(6));

        $this->getOption('values')->shouldReturn(array(6));
    }

    function it_allows_replacing_all_options_with_a_new_set()
    {
        $new_options = array(
            'input' => 'Fizz Buzz',
            'values' => array(6, 7),
            'field_id' => 5
        );

        $this->replaceOptions($new_options);

        $this->getOptions()->shouldReturn($new_options);
    }

    function it_allows_changing_many_options_at_once()
    {
        $this->setOptions(
            array(
                'values' => array(10),
                'field_id' => 3
            )
        );

        $this->getOptions()->shouldReturn(
            array(
                'input' => null,
                'values' => array(10),
                'field_id' => 3
            )
        );
    }

    function it_allows_removing_an_option_that_was_set_previously_and_reverts_to_default()
    {
        $this->setOption('values', array(1101, 2202));

        $this->getOptions()->shouldReturn(
            array(
                'input' => null,
                'values' => array(1101, 2202),
                'field_id' => 1
            )
        );

        // now remove it and see defaults again

        $this->removeOption('values');


        $this->getOptions()->shouldReturn(
            array(
                'input' => null,
                'values' => array(),
                'field_id' => 1
            )
        );
    }

    function it_can_serialize_itself()
    {
        $serialized = $this->serialize();

        $serialized->shouldBeLike(
            array(
                'op' => TermInterface::OP_IS,
                'options' => array(
                    'field_id' => 1
                )
            )
        );

        $this->setOp(TermInterface::OP_NOT);
        $this->setOption('values', array(4, 5, 6));

        $serialized = $this->serialize();

        $serialized->shouldBeLike(
            array(
                'op' => TermInterface::OP_NOT,
                'options' => array(
                    'field_id' => 1,
                    'values' => array(4, 5, 6)
                )
            )
        );
    }

    function it_lets_you_get_the_raw_options()
    {
        // you shouldn't really use this, use getOptions() instead

        $this->getRawOptions()->shouldBe(
            array(
                'field_id' => 1
            )
        );

        $this->setOption('values', array(5));
        $this->getRawOptions()->shouldBe(
            array(
                'field_id' => 1,
                'values' => array(5)
            )
        );

        // options you set are NOT validated until getOptions() is called
        $this->setOption('even_invalid_values_can_be_in_raw', 4);
        $this->getRawOptions()->shouldBe(
            array(
                'field_id' => 1,
                'values' => array(5),
                'even_invalid_values_can_be_in_raw' => 4
            )
        );
    }
}
