<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Theme;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use DeskPRO\Bundle\PortalBundle\Theme\TagHandlerInterface;
use DeskPRO\Bundle\PortalBundle\Theme\TagRequestFactory;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Theme\TagProcessor
 */
class TagProcessorSpec extends ObjectBehavior
{
    public function let(
        TagRequestFactory $tag_request_factory,
        TagHandlerInterface $esi_handler,
        TagHandlerInterface $inline_handler
    ) {
        $this->beConstructedWith($tag_request_factory, [$esi_handler, $inline_handler]);
    }

    public function it_returns_the_result_of_the_first_handler_that_supports_the_tag_request(
        TagRequestFactory $tag_request_factory,
        TagRequest $tag_request,
        Tag $tag,
        TagHandlerInterface $esi_handler, // esi handler was injected first
        TagHandlerInterface $inline_handler,
        Response $response
    ) {
        $arguments = [];
        $tag_request_factory->create($tag, $arguments)->willReturn($tag_request);

        $esi_handler->supports($tag, $tag_request)->willReturn(true);
        $esi_handler->handle($tag, $tag_request)->willReturn($response);

        $inline_handler->supports($tag, $tag_request)->willReturn(true);
        $inline_handler->handle($tag, $tag_request)->shouldNotBeCalled();

        $response->isSuccessful()->willReturn(true);
        $response->getStatusCode()->willReturn(200);
        $response->getContent()->willReturn('the end result');

        $this->process($tag, $arguments)->shouldReturn('the end result');
    }

    public function it_returns_an_empty_string_for_failed_responses(
        TagRequestFactory $tag_request_factory,
        TagRequest $tag_request,
        Tag $tag,
        TagHandlerInterface $esi_handler,
        Response $response
    ) {
        $arguments = [];
        $tag_request_factory->create($tag, $arguments)->willReturn($tag_request);

        $esi_handler->supports($tag, $tag_request)->willReturn(true);
        $esi_handler->handle($tag, $tag_request)->willReturn($response);

        $response->isSuccessful()->willReturn(false);
        $response->getStatusCode()->willReturn(200);
        $response->getContent()->willReturn('');

        $this->process($tag, $arguments)->shouldReturn('');
    }

    public function it_returns_an_empty_string_for_redirect_response(
        TagRequestFactory $tag_request_factory,
        TagRequest $tag_request,
        Tag $tag,
        TagHandlerInterface $esi_handler, // esi handler was injected first
        TagHandlerInterface $inline_handler,
        Response $response
    ) {
        $arguments = [];
        $tag_request_factory->create($tag, $arguments)->willReturn($tag_request);

        $esi_handler->supports($tag, $tag_request)->willReturn(true);
        $esi_handler->handle($tag, $tag_request)->willReturn($response);

        $inline_handler->supports($tag, $tag_request)->willReturn(true);
        $inline_handler->handle($tag, $tag_request)->shouldNotBeCalled();

        $response->isSuccessful()->willReturn(true);
        $response->getStatusCode()->willReturn(301);
        $response->getContent()->willReturn('the end result');

        $this->process($tag, $arguments)->shouldReturn('');
    }
}
