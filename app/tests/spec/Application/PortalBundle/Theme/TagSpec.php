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

namespace spec\Application\PortalBundle\Theme;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\PortalBundle\Theme\Tag;

/**
 * @mixin \Application\PortalBundle\Theme\Tag
 */
class TagSpec extends ObjectBehavior
{
    function it_can_be_serialized()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list'
        );

        $this->shouldHaveType('\Serializable');
    }

    function it_has_a_name()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list'
        );

        $this->getName()->shouldBe('knowledgebase');
    }

    function it_has_a_controller_name()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list'
        );

        $this->getControllerName()->shouldBe('Theme:Articles:list');
    }

    function it_has_a_list_of_its_defined_options()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            array('option1', 'option2')
        );

        $this->getDefinedOptions()->shouldBe(array('option1', 'option2'));
    }

    function it_has_an_associated_array_of_its_default_options()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            array('option1', 'option2'),
            array(
                'option1' => 'default here'
            )
        );

        $this->getDefaultOptions()->shouldBe(array('option1' => 'default here'));
    }

    function it_can_be_explicitely_marked_as_no_esi()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            array(),
            array(),
            $esi = false,
            $always_inline_guests = false
        );

        $this->isEsi($is_guest = true)->shouldBe(false);
        $this->isEsi()->shouldBe(false);
    }

    function it_can_be_marked_as_esi()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            array(),
            array(),
            $esi = true,
            $always_inline_guests = false
        );

        $this->isEsi()->shouldBe(true);
        $this->isEsi($is_guest = true)->shouldBe(true);
    }

    function it_can_be_marked_as_esi_but_you_can_turn_esi_off_for_guests()
    {
        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            array(),
            array(),
            $esi = true,
            $always_inline_guests = true
        );

        $this->isEsi()->shouldBe(true);
        $this->isEsi($is_guest = true)->shouldBe(false);
    }

    function it_disallows_route_params_by_default()
    {
        // by default, _route_params are ignored when generating ESI tag urls

        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            array(),
            array(),
            $esi = true
        );

        $this->allowRouteParams()->shouldBe(false);
    }

    function it_can_be_marked_to_allow_route_params()
    {
        // by default, _route_params are ignored when generating ESI tag urls
        // you can flag a tag to allow these to be in the url

        $this->beConstructedWith(
            'knowledgebase',
            'Theme:Articles:list',
            array(),
            array(),
            $esi = true,
            $always_inline_guests = false,
            $allow_route_params = true
        );

        $this->allowRouteParams()->shouldBe(true);
    }
}
