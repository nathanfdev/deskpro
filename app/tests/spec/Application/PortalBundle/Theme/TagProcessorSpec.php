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

use Application\PortalBundle\Request\TagRequest;
use Application\PortalBundle\Theme\Tag;
use Application\PortalBundle\Theme\TagHandler\EsiTagHandler;
use Application\PortalBundle\Theme\TagHandler\InlineTagHandler;
use Application\PortalBundle\Theme\TagHandlerInterface;
use Application\PortalBundle\Theme\TagRequestFactory;
use Application\PortalBundle\Theme\ThemeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\PortalBundle\Theme\TagProcessor;
use Symfony\Component\HttpFoundation\Response;

/**
 * @mixin \Application\PortalBundle\Theme\TagProcessor
 */
class TagProcessorSpec extends ObjectBehavior
{
    function let(
        TagRequestFactory $tag_request_factory,
        TagHandlerInterface $esi_handler,
        TagHandlerInterface $inline_handler
    )
    {
        $this->beConstructedWith($tag_request_factory, array($esi_handler, $inline_handler));
    }

    function it_returns_the_result_of_the_first_handler_that_supports_the_tag_request(
        TagRequestFactory $tag_request_factory,
        TagRequest $tag_request,
        Tag $tag,
        TagHandlerInterface $esi_handler, // esi handler was injected first
        TagHandlerInterface $inline_handler,
        Response $response
    )
    {
        $arguments = array();
        $tag_request_factory->create($tag, $arguments)->willReturn($tag_request);

        $esi_handler->supports($tag, $tag_request)->willReturn(true);
        $esi_handler->handle($tag, $tag_request)->willReturn($response);

        $inline_handler->supports($tag, $tag_request)->willReturn(true);
        $inline_handler->handle($tag, $tag_request)->shouldNotBeCalled();

        $response->isSuccessful()->willReturn(true);
        $response->getContent()->willReturn('the end result');

        $this->process($tag, $arguments)->shouldReturn('the end result');
    }

    function it_returns_an_empty_string_for_failed_responses(
        TagRequestFactory $tag_request_factory,
        TagRequest $tag_request,
        Tag $tag,
        TagHandlerInterface $esi_handler,
        Response $response
    )
    {
        $arguments = array();
        $tag_request_factory->create($tag, $arguments)->willReturn($tag_request);

        $esi_handler->supports($tag, $tag_request)->willReturn(true);
        $esi_handler->handle($tag, $tag_request)->willReturn($response);

        $response->isSuccessful()->willReturn(false);

        $this->process($tag, $arguments)->shouldReturn('');
    }
}
