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
namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine;

use DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool
 */
class TermCompilerHelperPoolSpec extends ObjectBehavior
{
    public function let(
        \DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface $helper1,
        TermCompilerHelperInterface $helper2
    ) {
        $helper1->getId()->willReturn('helper1');
        $helper2->getId()->willReturn('helper2');

        $this->beConstructedWith(array($helper1, $helper2));
    }

    public function it_allows_you_to_get_helpers(
        TermCompilerHelperInterface $helper1,
        \DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface $helper2
    ) {
        $this->getHelper('helper1')->shouldBe($helper1);
        $this->getHelper('helper2')->shouldBe($helper2);
    }

    public function it_throws_exception_if_id_does_not_exist()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('getHelper', array('invalid'));
    }
}
