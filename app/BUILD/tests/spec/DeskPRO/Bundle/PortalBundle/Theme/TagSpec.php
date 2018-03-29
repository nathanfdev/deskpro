<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Theme\Tag
 */
class TagSpec extends ObjectBehavior
{
    public function it_can_be_serialized()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list'
        );

        $this->shouldHaveType('\Serializable');
    }

    public function it_has_a_name()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list'
        );

        $this->getName()->shouldBe('knowledgebase');
    }

    public function it_has_a_controller_name()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list'
        );

        $this->getControllerName()->shouldBe('Theme:Articles:list');
    }

    public function it_has_a_list_of_its_defined_options()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            ['option1', 'option2']
        );

        $this->getDefinedOptions()->shouldBe(['option1', 'option2']);
    }

    public function it_has_an_associated_array_of_its_default_options()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            ['option1', 'option2'],
            [
                'option1' => 'default here',
            ]
        );

        $this->getDefaultOptions()->shouldBe(['option1' => 'default here']);
    }

    public function it_can_be_explicitely_marked_as_no_esi()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            [],
            [],
            $esi = false,
            $always_inline_guests = false
        );

        $this->isEsi($is_guest = true)->shouldBe(false);
        $this->isEsi()->shouldBe(false);
    }

    public function it_can_be_marked_as_esi()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            [],
            [],
            $esi = true,
            $always_inline_guests = false
        );

        $this->isEsi()->shouldBe(true);
        $this->isEsi($is_guest = true)->shouldBe(true);
    }

    public function it_can_be_marked_as_esi_but_you_can_turn_esi_off_for_guests()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            [],
            [],
            $esi = true,
            $always_inline_guests = true
        );

        $this->isEsi()->shouldBe(true);
        $this->isEsi($is_guest = true)->shouldBe(false);
    }

    public function it_disallows_route_params_by_default()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            [],
            [],
            $esi = true
        );

        $this->allowRouteParams()->shouldBe(false);
    }

    public function it_can_be_marked_to_allow_route_params()
    {
        // by default, _route_params are ignored when generating ESI tag urls
        // you can flag a tag to allow these to be in the url

        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            [],
            [],
            $esi = true,
            $always_inline_guests = true,
            $allow_route_params = true
        );

        $this->allowRouteParams()->shouldBe(true);
    }
}
