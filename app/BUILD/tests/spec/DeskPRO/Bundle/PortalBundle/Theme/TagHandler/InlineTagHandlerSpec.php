<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Theme\TagHandler;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Theme\TagHandler\InlineTagHandler
 */
class InlineTagHandlerSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $container,
        HttpKernel $http_kernel,
        TagRequest $tag_request,
        ParameterBag $attributes,
        Tag $tag
    ) {
        $container->get('http_kernel')->willReturn($http_kernel);

        $tag_request->attributes = $attributes;

        $this->beConstructedWith($container);
    }

    public function it_supports_any_tag(
        Tag $tag,
        TagRequest $tag_request
    ) {
        $this->supports($tag, $tag_request)->shouldBe(true);
    }

    public function it_adds_the_proper_controller_attribute_to_tag_request(
        TagRequest $tag_request,
        ParameterBag $attributes,
        Tag $tag
    ) {
        $tag->getControllerName()->willReturn('Theme:Portal:common');

        $attributes->set('_controller', 'Theme:Portal:common')->shouldBeCalled();

        $this->handle($tag, $tag_request);
    }

    public function it_handles_the_request_with_the_http_kernel_as_a_subrequest(
        TagRequest $tag_request,
        Tag $tag,
        HttpKernel $http_kernel
    ) {
        $http_kernel->handle($tag_request, HttpKernelInterface::SUB_REQUEST)->shouldBeCalled();

        $this->handle($tag, $tag_request);
    }

    public function it_returns_the_kernel_response(
        TagRequest $tag_request,
        Tag $tag,
        HttpKernel $http_kernel,
        Response $response
    ) {
        $http_kernel->handle($tag_request, HttpKernelInterface::SUB_REQUEST)->willReturn($response);

        $this->handle($tag, $tag_request)->shouldReturn($response);
    }
}
