<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Themes\Standard;

use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Themes\Standard\StandardTheme
 */
class StandardThemeSpec extends ObjectBehavior
{
    public function it_is_a_theme()
    {
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface');
    }

    public function it_has_the_correct_id_and_name()
    {
        $this->getId()->shouldReturn('standard');
        $this->getName()->shouldReturn('Standard');
    }

    public function it_is_a_child_of_the_base_theme()
    {
        $this->getParentId()->shouldReturn('base');
    }

    public function it_can_resolve_a_tag_which_will_recursively_climb_the_parent_tree(
        Tag $tag1,
        Tag $tag2,
        ThemeInterface $base_theme
    ) {
        // base holds tag 2
        $tag2->getName()->willReturn('second_tag');
        $base_theme->resolveTag('second_tag')->willReturn($tag2);
        $this->setParent($base_theme);

        // standard holds tag 1
        $tag1->getName()->willReturn('first_tag');
        $this->setTags([$tag1]);
        $this->getTags()->shouldBe([
            'first_tag' => $tag1,
        ]);

        // both can be resolved from standard, transparently
        $this->resolveTag('first_tag')->shouldReturn($tag1);
        $this->resolveTag('second_tag')->shouldReturn($tag2);
    }
}
