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

use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\Brand\BrandContainer;
use Application\PortalBundle\Theme\Tag;
use Application\PortalBundle\Theme\ThemeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\PortalBundle\Theme\ThemeView;

/**
 * @mixin \Application\PortalBundle\Theme\ThemeView
 */
class ThemeViewSpec extends ObjectBehavior
{
    function let(
        BrandStack $brand_stack,
        BrandContainer $brand_container,
        ThemeInterface $theme,
        Tag $tag
    )
    {
        $brand_stack->getActive()->willReturn($brand_container);
        $brand_container->getTheme()->willReturn($theme);
        $theme->resolveTag('tag_name')->willReturn($tag);
        $tag->getDefinedOptions()->willReturn(array('a', 'b', 'c', 'd', 'e'));

        $this->beConstructedWith($brand_stack, array(
            'a' => 'default',
            'd' => 'the controller sets these page defaults'
        ));
    }

    function it_will_call_a_tag_using_the_constructed_default_options(
        BrandContainer $brand_container,
        $default_options
    )
    {
        $brand_container->renderTag('tag_name', array(
            'a' => 'default',
            'd' => 'the controller sets these page defaults'
        ))->shouldBeCalled();

        $no_explicit_options = array(array()); // uses first arg in the array (because twig, see commnets in class)

        $this->__call('tag_name', $no_explicit_options);
    }

    function it_allows_tags_to_be_called_with_explicit_options_that_will_override_default_options(
        BrandContainer $brand_container,
        $default_options
    )
    {
        $brand_container->renderTag('tag_name', array(
            'a' => 'NEW VAL',
            'b' => 'something',
            'd' => 'the controller sets these page defaults'
        ))->shouldBeCalled();

        $this->__call('tag_name', array(array( // uses first arg in the array (because twig, see commnets in class)
            'a' => 'NEW VAL',
            'b' => 'something'
        )));
    }
}
