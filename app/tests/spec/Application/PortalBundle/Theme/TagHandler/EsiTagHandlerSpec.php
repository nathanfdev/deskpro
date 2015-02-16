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

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\News;
use Application\PortalBundle\HttpCache\PortalCacheHelper;
use Application\PortalBundle\Request\TagRequest;
use Application\PortalBundle\Theme\Tag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\PortalBundle\Theme\TagHandler\EsiTagHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer;

/**
 * @mixin \Application\PortalBundle\Theme\TagHandler\EsiTagHandler
 */
class EsiTagHandlerSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        EsiFragmentRenderer $esi_renderer,
        PortalCacheHelper $cache_helper,
        Tag $tag,
        TagRequest $tag_request
    )
    {
        $container->get('fragment.renderer.esi')->willReturn($esi_renderer);
        $cache_helper->isGuestRequest()->willReturn(false);
        $this->beConstructedWith($container, $cache_helper);
    }

    function it_supports_tags_that_say_they_are_esi_for_guests(
        PortalCacheHelper $cache_helper,
        Tag $tag,
        TagRequest $tag_request
    )
    {
        $cache_helper->isGuestRequest()->willReturn($is_guest = true);
        $tag->isEsi($is_guest)->willReturn(true);

        $this->supports($tag, $tag_request)->shouldReturn(true);
    }

    function it_supports_tags_that_say_they_are_esi_for_non_guests(
        PortalCacheHelper $cache_helper,
        Tag $tag,
        TagRequest $tag_request
    )
    {
        $cache_helper->isGuestRequest()->willReturn($is_guest = false);
        $tag->isEsi($is_guest)->willReturn(true);

        $this->supports($tag, $tag_request)->shouldReturn(true);
    }

    function it_wont_support_tags_that_say_they_are_not_esi(
        PortalCacheHelper $cache_helper,
        Tag $tag,
        TagRequest $tag_request
    )
    {
        $tag->isEsi(Argument::any())->willReturn(false);

        $this->supports($tag, $tag_request)->shouldReturn(false);
    }

    function it_filters_domain_entities_into_their_ids_removes_other_objects_and_passes_to_esi_renderer(
        PortalCacheHelper $cache_helper,
        EsiFragmentRenderer $esi_renderer,
        Tag $tag,
        TagRequest $tag_request,
        ParameterBag $attributes,
        ParameterBag $query,
        Response $response,
        Article $article,
        News $news
    )
    {
        $tag->getName()->willReturn('tag_name');
        $tag->getControllerName()->shouldBeCalled();

        $tag_request->attributes = $attributes;
        $tag_request->query = $query;

        $news->getId()->willReturn(10);
        $attributes->all()->willReturn(array(
            'news' => $news,
            'string' => 'string',
            'visitor_id' => 'special key that will be removed as well',
            'num' => 3,
            'will_be_ignored' => new \SplStack()
        ));

        $article->getId()->willReturn(5);
        $query->all()->willReturn(array(
            'article' => $article,
            'param' => 'param'
        ));

        // we use objects in a /_proxy esi url, so they need to be filtered out
        $attributes->replace(
            array(
                'news' => 10,
                'string' => 'string',
                'num' => 3
            )
        )->shouldBeCalled();

        $query->replace(
            array(
                'article' => 5,
                'param' => 'param'
            )
        )->shouldBeCalled();

        $esi_renderer->render(
            Argument::type('Symfony\Component\HttpKernel\Controller\ControllerReference'),
            $tag_request,
            array('ignore_errors' => true)
        )->willReturn($response);

        $this->handle($tag, $tag_request)->shouldReturn($response);
    }
}
