<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace spec\Application\PortalBundle\Themes\Base;

use Application\PortalBundle\Theme\Tag;
use Application\PortalBundle\Theme\ThemeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\PortalBundle\Themes\Base\BaseTheme;

/**
 * @mixin \Application\PortalBundle\Themes\Base\BaseTheme
 */
class BaseThemeSpec extends ObjectBehavior
{

    // we test the base theme more thouroughly, the others work similarly
    // so we don't test them as much (we're basically targeting AbstractTheme)

    function it_is_a_theme_that_extends_abstract_theme()
    {
        $this->shouldHaveType('Application\PortalBundle\Theme\ThemeInterface');
        $this->shouldHaveType('Application\PortalBundle\Theme\AbstractTheme');
    }

    function it_is_serializable()
    {
        $this->shouldHaveType('\Serializable');
    }

    function it_has_an_id_and_a_name()
    {
        $this->getId()->shouldReturn('base');
        $this->getName()->shouldReturn('Base');
    }

    function it_does_not_have_a_parent_because_it_is_base_theme_and_parents_are_hardcoded()
    {
        $this->getParent()->shouldBe(null);
        $this->getParentId()->shouldBe(null);
    }

    function it_knows_its_base_template_dir()
    {
        $this->getBaseTemplateDir()->shouldBeString();
    }

    function it_knows_its_base_controller_dir()
    {
        $this->getBaseControllerDir()->shouldBeString();
    }

    function it_knows_its_namespace()
    {
        $this->getNamespace()->shouldBeString();
    }

    function it_can_contain_hard_coded_tags_but_we_dont_use_them()
    {
        $this->getHardCodedTags()->shouldBe(array());
    }

    function it_does_of_course_allow_adding_tags_after_construction(
        Tag $tag1,
        Tag $tag2,
        Tag $tag3
    )
    {
        $tag1->getName()->willReturn('first_tag');
        $tag2->getName()->willReturn('second_tag');
        $tag3->getName()->willReturn('third_tag');

        $this->setTags($tags = array($tag1, $tag2, $tag3));
        $this->getTags()->shouldBe(array(
            'first_tag' => $tag1,
            'second_tag' => $tag2,
            'third_tag' => $tag3
        ));

        $this->getTag('first_tag')->shouldReturn($tag1);
        $this->getTag('second_tag')->shouldReturn($tag2);
        $this->getTag('third_tag')->shouldReturn($tag3);
    }

    function it_can_resolve_a_tag_which_will_recursively_climb_the_parent_tree(
        Tag $tag1,
        Tag $tag2
    )
    {
        // base does not have a parent, so check out the StandardTheme spec
        // a better example (since no parent here, it is the same as getTag())

        $tag1->getName()->willReturn('first_tag');
        $tag2->getName()->willReturn('second_tag');

        $this->setTags($tags = array($tag1, $tag2));
        $this->getTags()->shouldBe(array(
            'first_tag' => $tag1,
            'second_tag' => $tag2
        ));

        $this->resolveTag('first_tag')->shouldReturn($tag1);
        $this->resolveTag('second_tag')->shouldReturn($tag2);
    }
}
