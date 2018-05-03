<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Routing;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Routing\PortalUrlBuilder
 */
class PortalUrlBuilderSpec extends ObjectBehavior
{
    public function it_returns_path_if_no_lang_or_mode()
    {
        $this->beConstructedWith('/new-ticket');

        $this->__toString()->shouldBe('/new-ticket');
    }

    public function it_returns_with_language_appended(
        Language $lang
    ) {
        $lang->getUrlCode()->willReturn('fr');

        $this->beConstructedWith('/new-ticket', $lang);

        $this->__toString()->shouldReturn('/fr/new-ticket');
    }

    public function it_returns_with_mode_appended(
        PortalMode $mode
    ) {
        $mode->getModePath()->willReturn('/admin-mode');

        $this->beConstructedWith('/new-ticket', null, $mode);

        $this->__toString()->shouldReturn('/admin-mode/new-ticket');
    }

    public function it_returns_with_language_and_mode_appended(
        PortalMode $mode,
        Language $lang
    ) {
        $mode->getModePath()->willReturn('/admin-mode');
        $lang->getUrlCode()->willReturn('fr');

        $this->beConstructedWith('/new-ticket', $lang, $mode);

        $this->__toString()->shouldReturn('/admin-mode/fr/new-ticket');
    }

    public function it_works_for_root_url_and_never_appends_a_slash(
        PortalMode $mode,
        Language $lang
    ) {
        $this->beConstructedWith('/', $lang, $mode);
        $mode->getModePath()->willReturn('/admin-mode');
        $lang->getUrlCode()->willReturn('fr');

        $this->__toString()->shouldReturn('/admin-mode/fr');
    }
}
