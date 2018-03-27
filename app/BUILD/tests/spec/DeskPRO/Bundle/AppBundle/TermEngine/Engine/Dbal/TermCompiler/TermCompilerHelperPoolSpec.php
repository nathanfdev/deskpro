<?php

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

        $this->beConstructedWith([$helper1, $helper2]);
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
        $this->shouldThrow('\InvalidArgumentException')->during('getHelper', ['invalid']);
    }
}
