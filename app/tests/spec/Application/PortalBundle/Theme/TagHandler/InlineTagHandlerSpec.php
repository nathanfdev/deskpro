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

namespace spec\Application\PortalBundle\Theme\TagHandler;

use Application\PortalBundle\Request\TagRequest;
use Application\PortalBundle\Theme\Tag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @mixin \Application\PortalBundle\Theme\TagHandler\InlineTagHandler
 */
class InlineTagHandlerSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        HttpKernel $http_kernel,
        TagRequest $tag_request,
        ParameterBag $attributes,
        Tag $tag
    )
    {
        $container->get('http_kernel')->willReturn($http_kernel);

        $tag_request->attributes = $attributes;

        $this->beConstructedWith($container);
    }

    function it_supports_any_tag(
        Tag $tag,
        TagRequest $tag_request
    )
    {
        $this->supports($tag, $tag_request)->shouldBe(true);
    }

    function it_adds_the_proper_controller_attribute_to_tag_request(
        TagRequest $tag_request,
        ParameterBag $attributes,
        Tag $tag
    )
    {
        $tag->getControllerName()->willReturn('Theme:Portal:common');

        $attributes->set('_controller', 'Theme:Portal:common')->shouldBeCalled();

        $this->handle($tag, $tag_request);
    }

    function it_handles_the_request_with_the_http_kernel_as_a_subrequest(
        TagRequest $tag_request,
        Tag $tag,
        HttpKernel $http_kernel
    )
    {
        $http_kernel->handle($tag_request, HttpKernelInterface::SUB_REQUEST)->shouldBeCalled();

        $this->handle($tag, $tag_request);
    }

    function it_returns_the_kernel_response(
        TagRequest $tag_request,
        Tag $tag,
        HttpKernel $http_kernel,
        Response $response
    )
    {
        $http_kernel->handle($tag_request, HttpKernelInterface::SUB_REQUEST)->willReturn($response);

        $this->handle($tag, $tag_request)->shouldReturn($response);
    }
}
