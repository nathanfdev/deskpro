<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Themes\Base;

use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Themes\Base\BaseTheme
 */
class BaseThemeSpec extends ObjectBehavior
{
    // we test the base theme more thouroughly, the others work similarly
    // so we don't test them as much (we're basically targeting AbstractTheme)

    public function it_is_a_theme_that_extends_abstract_theme()
    {
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface');
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\Theme\AbstractTheme');
    }

    public function it_is_serializable()
    {
        $this->shouldHaveType('\Serializable');
    }

    public function it_has_an_id_and_a_name()
    {
        $this->getId()->shouldReturn('base');
        $this->getName()->shouldReturn('Base');
    }

    public function it_does_not_have_a_parent_because_it_is_base_theme_and_parents_are_hardcoded()
    {
        $this->getParent()->shouldBe(null);
        $this->getParentId()->shouldBe(null);
    }

    public function it_knows_its_base_template_dir()
    {
        $this->getBaseTemplateDir()->shouldBeString();
    }

    public function it_knows_its_base_controller_dir()
    {
        $this->getBaseControllerDir()->shouldBeString();
    }

    public function it_knows_its_namespace()
    {
        $this->getNamespace()->shouldBeString();
    }

    public function it_can_contain_hard_coded_tags_but_we_dont_use_them()
    {
        $this->getHardCodedTags()->shouldBe([]);
    }

    public function it_does_of_course_allow_adding_tags_after_construction(
        Tag $tag1,
        Tag $tag2,
        Tag $tag3
    ) {
        $tag1->getName()->willReturn('first_tag');
        $tag2->getName()->willReturn('second_tag');
        $tag3->getName()->willReturn('third_tag');

        $this->setTags($tags = [$tag1, $tag2, $tag3]);
        $this->getTags()->shouldBe([
            'first_tag'  => $tag1,
            'second_tag' => $tag2,
            'third_tag'  => $tag3,
        ]);

        $this->getTag('first_tag')->shouldReturn($tag1);
        $this->getTag('second_tag')->shouldReturn($tag2);
        $this->getTag('third_tag')->shouldReturn($tag3);
    }

    public function it_can_resolve_a_tag_which_will_recursively_climb_the_parent_tree(
        Tag $tag1,
        Tag $tag2
    ) {
        // base does not have a parent, so check out the StandardTheme spec
        // a better example (since no parent here, it is the same as getTag())

        $tag1->getName()->willReturn('first_tag');
        $tag2->getName()->willReturn('second_tag');

        $this->setTags($tags = [$tag1, $tag2]);
        $this->getTags()->shouldBe([
            'first_tag'  => $tag1,
            'second_tag' => $tag2,
        ]);

        $this->resolveTag('first_tag')->shouldReturn($tag1);
        $this->resolveTag('second_tag')->shouldReturn($tag2);
    }
}
