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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpCheck;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpCheck
 */
class PhpCheckSpec extends ObjectBehavior
{
    function it_is_just_a_holder_for_php_code()
    {
        $this->getCheckCode()->shouldBe(null);

        $this->setCheckCode('$check = true;');

        $this->getCheckCode()->shouldBe('$check = true;');
    }

    function it_can_be_constructed_with_the_check_code()
    {
        $this->beConstructedWith(
            '$check = true;'
        );

        $this->getCheckCode()->shouldBe('$check = true;');
    }

    function it_does_a_to_string_that_trims_the_code()
    {
        $this->setCheckCode('$check = true;');
        $this->__toString()->shouldBe('$check = true;');


        $this->setCheckCode(
            '



        $check = true;                                     '
        );
        $this->__toString()->shouldBe('$check = true;');
    }
}
