<?php

namespace spec\DeskPRO\Bundle\PortalBundle\Routing;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Language\LanguageStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Routing\PortalUrlBuilder
 */
class PortalUrlBuilderSpec extends ObjectBehavior
{
    public function let(
        LanguageManager   $languageManager,
        PortalModeStorage $modeStorage,
        RouterInterface   $router,
        RequestContext    $requestContext,
        PortalMode        $portalMode,
        LanguageStack     $languageStack,
        Language          $language
    ) {
        $this->beConstructedWith($languageManager, $modeStorage, $router);
        $router->getContext()->willReturn($requestContext);
        $modeStorage->getMode()->willReturn($portalMode);
        $languageManager->getLanguageStack()->willReturn($languageStack);
        $languageStack->getActive()->willReturn($language);
    }

    public function it_returns_path_if_no_lang_or_mode(LanguageManager $languageManager, RequestContext $requestContext)
    {
        $languageManager->isMultiLanguagePortal()->willReturn(false);
        $requestContext->getBaseUrl()->willReturn('');

        $this->buildUrl('/new-ticket')->shouldReturn('/new-ticket');
    }

    public function it_returns_with_language_appended(
        LanguageManager $languageManager,
        Language        $language,
        RequestContext  $requestContext
    ) {
        $requestContext->getBaseUrl()->willReturn('');
        $languageManager->isMultiLanguagePortal()->willReturn(true);
        $language->getUrlCode()->willReturn('fr');

        $this->buildUrl('/new-ticket')->shouldReturn('/fr/new-ticket');
    }

    public function it_returns_with_mode_appended(LanguageManager $languageManager, PortalMode $portalMode)
    {
        $languageManager->isMultiLanguagePortal()->willReturn(false);
        $portalMode->getModePath()->willReturn('/admin-mode');

        $this->buildUrl('/new-ticket')->shouldReturn('/admin-mode/new-ticket');
    }

    public function it_returns_with_language_and_mode_appended(
        LanguageManager $languageManager,
        Language        $language,
        PortalMode      $portalMode
    ) {
        $languageManager->isMultiLanguagePortal()->willReturn(true);
        $portalMode->getModePath()->willReturn('/admin-mode');
        $language->getUrlCode()->willReturn('fr');

        $this->buildUrl('/new-ticket')->shouldReturn('/admin-mode/fr/new-ticket');
    }

    public function it_works_for_root_url_and_never_appends_a_slash(
        LanguageManager $languageManager,
        Language        $language,
        PortalMode      $portalMode
    ) {
        $languageManager->isMultiLanguagePortal()->willReturn(true);
        $portalMode->getModePath()->willReturn('/admin-mode');
        $language->getUrlCode()->willReturn('fr');

        $this->buildUrl('/')->shouldReturn('/admin-mode/fr');
    }

    public function it_adds_base_url(LanguageManager $languageManager, RequestContext $requestContext)
    {
        $languageManager->isMultiLanguagePortal()->willReturn(false);
        $requestContext->getBaseUrl()->willReturn('/b/my-brand');

        $this->buildUrl('/new-ticket')->shouldReturn('/b/my-brand/new-ticket');
    }

    public function it_handles_if_base_url_is_already_present_in_path(LanguageManager $languageManager, RequestContext $requestContext)
    {
        $languageManager->isMultiLanguagePortal()->willReturn(false);
        $requestContext->getBaseUrl()->willReturn('/b/my-brand');

        $this->buildUrl('/b/my-brand/new-ticket')->shouldReturn('/b/my-brand/new-ticket');
    }

    public function it_handles_if_base_url_in_path_dont_have_slash(LanguageManager $languageManager, RequestContext $requestContext)
    {
        $languageManager->isMultiLanguagePortal()->willReturn(false);
        $requestContext->getBaseUrl()->willReturn('/b/my-brand');

        $this->buildUrl('b/my-brand/new-ticket')->shouldReturn('/b/my-brand/new-ticket');
    }

    public function it_returns_url_parts_in_proper_order(
        LanguageManager $languageManager,
        Language        $language,
        PortalMode      $portalMode,
        RequestContext  $requestContext
    ) {
        $requestContext->getBaseUrl()->willReturn('/b/my-brand');
        $languageManager->isMultiLanguagePortal()->willReturn(true);
        $portalMode->getModePath()->willReturn('/admin-mode');
        $language->getUrlCode()->willReturn('fr');

        $this->buildUrl('/new-ticket')->shouldReturn('/b/my-brand/admin-mode/fr/new-ticket');
    }
}
