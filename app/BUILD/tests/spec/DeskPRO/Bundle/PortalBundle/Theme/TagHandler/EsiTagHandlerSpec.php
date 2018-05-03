<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Theme\TagHandler;

use DeskPRO\Bundle\PortalBundle\HttpCache\PortalCacheHelper;
use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Bundle\PortalBundle\Theme\Tag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Theme\TagHandler\EsiTagHandler
 */
class EsiTagHandlerSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $container,
        EsiFragmentRenderer $esi_renderer,
        PortalCacheHelper $cache_helper,
        Tag $tag,
        TagRequest $tag_request,
        PortalModeStorage $mode_storage,
        PortalMode $mode
    ) {
        $container->get('fragment.renderer.esi')->willReturn($esi_renderer);
        $cache_helper->isGuestRequest()->willReturn(false);
        // phpspec doesnt like serailzied so just use the normal mode here, in reality it would be serialized()
        $mode_storage->getSerializedMode()->willReturn($mode);

        $this->beConstructedWith($container, $cache_helper, $mode_storage);
    }

    public function it_supports_tags_that_say_they_are_esi_for_guests(
        PortalCacheHelper $cache_helper,
        Tag $tag,
        TagRequest $tag_request
    ) {
        $cache_helper->isGuestRequest()->willReturn($is_guest = true);
        $tag->isEsi($is_guest)->willReturn(true);

        $this->supports($tag, $tag_request)->shouldReturn(true);
    }

    public function it_supports_tags_that_say_they_are_esi_for_non_guests(
        PortalCacheHelper $cache_helper,
        Tag $tag,
        TagRequest $tag_request
    ) {
        $cache_helper->isGuestRequest()->willReturn($is_guest = false);
        $tag->isEsi($is_guest)->willReturn(true);

        $this->supports($tag, $tag_request)->shouldReturn(true);
    }

    public function it_wont_support_tags_that_say_they_are_not_esi(
        PortalCacheHelper $cache_helper,
        Tag $tag,
        TagRequest $tag_request
    ) {
        $tag->isEsi(Argument::any())->willReturn(false);

        $this->supports($tag, $tag_request)->shouldReturn(false);
    }
}
