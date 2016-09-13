<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Routing;

use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Routing\PortalRequestInfo
 */
class PortalRequestInfoSpec extends ObjectBehavior
{
    public function let(
        Request $request,
        RouterInterface $router,
        PortalMode $mode,
        PortalModeFactory $mode_factory,
        ParameterBag $bag
    ) {
        $this->beConstructedWith($request, $mode, $mode_factory);
        $request->attributes = $bag;
    }

    public function it_finds_the_lang_code_in_the_url(
        Request $request,
        PortalModeFactory $mode_factory
    ) {
        $request->getPathInfo()->willReturn('/en/articles');
        $mode_factory->getInternalPath('/en/articles')->willReturn('/en/articles');
        $this->getLanguageUrlCode()->shouldReturn('en');

        $request->getPathInfo()->willReturn('/en');
        $mode_factory->getInternalPath('/en')->willReturn('/en');
        $this->getLanguageUrlCode()->shouldReturn('en');

        $request->getPathInfo()->willReturn('/en/');
        $mode_factory->getInternalPath('/en/')->willReturn('/en/');
        $this->getLanguageUrlCode()->shouldReturn('en');

        $request->getPathInfo()->willReturn('/articles/en');
        $mode_factory->getInternalPath('/articles/en')->willReturn('/articles/en');
        $this->getLanguageUrlCode()->shouldReturn(null);

        $request->getPathInfo()->willReturn('/');
        $mode_factory->getInternalPath('/')->willReturn('/');
        $this->getLanguageUrlCode()->shouldReturn(null);

        $request->getPathInfo()->willReturn('/admin-mode');
        $mode_factory->getInternalPath('/admin-mode')->willReturn('/admin-mode');
        $this->getLanguageUrlCode()->shouldReturn(null);
    }

    public function it_finds_the_routable_path_in_the_url(
        Request $request,
        PortalModeFactory $mode_factory
    ) {
        $request->getPathInfo()->willReturn($path_under_test = '/en/articles');
        $mode_factory->getInternalPath($path_under_test)->willReturn($path_under_test);
        $this->getRoutablePath()->shouldReturn('/articles');

        $request->getPathInfo()->willReturn($path_under_test = '/en');
        $mode_factory->getInternalPath($path_under_test)->willReturn($path_under_test);
        $this->getRoutablePath()->shouldReturn('/');

        $request->getPathInfo()->willReturn($path_under_test = '/en/');
        $mode_factory->getInternalPath($path_under_test)->willReturn($path_under_test);
        $this->getRoutablePath()->shouldReturn('/');

        $request->getPathInfo()->willReturn($path_under_test = '/articles/en');
        $mode_factory->getInternalPath($path_under_test)->willReturn($path_under_test);
        $this->getRoutablePath()->shouldReturn('/articles/en');

        $request->getPathInfo()->willReturn($path_under_test = '/');
        $mode_factory->getInternalPath($path_under_test)->willReturn($path_under_test);
        $this->getRoutablePath()->shouldReturn('/');

        $request->getPathInfo()->willReturn($path_under_test = '/admin-mode');
        $mode_factory->getInternalPath($path_under_test)->willReturn($path_under_test);
        $this->getRoutablePath()->shouldReturn('/admin-mode');
    }

    public function it_will_use_the_modes_internal_path_instead_of_the_request_pathinfo_if_given(
        Request $request,
        PortalMode $mode,
        PortalModeFactory $mode_factory
    ) {
        $request->getPathInfo()->willReturn($path_under_test = '/admin-mode/en/articles');
        $mode_factory->getInternalPath($path_under_test)->willReturn('/en/articles');

        $this->getLanguageUrlCode()->shouldReturn('en');
        $this->getRoutablePath()->shouldReturn('/articles');

        $request->getPathInfo()->willReturn($path_under_test = '/en/');
        $mode_factory->getInternalPath($path_under_test)->willReturn($path_under_test);
        $this->getLanguageUrlCode()->shouldReturn('en');
        $this->getRoutablePath()->shouldReturn('/');

        $request->getPathInfo()->willReturn($path_under_test = '/articles');
        $mode_factory->getInternalPath($path_under_test)->willReturn($path_under_test);
        $this->getLanguageUrlCode()->shouldReturn(null);
        $this->getRoutablePath()->shouldReturn('/articles');
    }

    public function it_will_report_a_proxy_url_as_special(
        Request $request
    ) {
        $request->getPathInfo()->willReturn('/_proxy?something=bar');
        $this->isSpecialPath()->shouldReturn(true);

        $request->getPathInfo()->willReturn('/en/_proxy?something=bar');
        $this->isSpecialPath()->shouldReturn(false);

        $request->getPathInfo()->willReturn('/en');
        $this->isSpecialPath()->shouldReturn(false);

        $request->getPathInfo()->willReturn('/');
        $this->isSpecialPath()->shouldReturn(false);

        $request->getPathInfo()->willReturn('/admin-mode/en');
        $this->isSpecialPath()->shouldReturn(false);

        $request->getPathInfo()->willReturn('/downloads');
        $this->isSpecialPath()->shouldReturn(false);
    }

    public function it_will_report_any_url_starting_with_underscore_as_special(
        Request $request
    ) {
        $request->getPathInfo()->willReturn('/_profile');
        $this->isSpecialPath()->shouldReturn(true);

        $request->getPathInfo()->willReturn('/_wdt');
        $this->isSpecialPath()->shouldReturn(true);

        $request->getPathInfo()->willReturn('/_internal');
        $this->isSpecialPath()->shouldReturn(true);

        $request->getPathInfo()->willReturn('/en/_proxy?something=bar');
        $this->isSpecialPath()->shouldReturn(false);

        $request->getPathInfo()->willReturn('/en');
        $this->isSpecialPath()->shouldReturn(false);

        $request->getPathInfo()->willReturn('/');
        $this->isSpecialPath()->shouldReturn(false);

        $request->getPathInfo()->willReturn('/admin-mode/en');
        $this->isSpecialPath()->shouldReturn(false);

        $request->getPathInfo()->willReturn('/downloads');
        $this->isSpecialPath()->shouldReturn(false);
    }

    public function it_uses_the_router_to_see_if_a_url_is_of_a_special_route(
        Request $request,
        RouterInterface $router
    ) {
        $special_routes = [
            'saml_sls',
            'saml_metadata',
            'portal_agent_login',
            'user_saml_sls',
            'saml_sls',
            'user_saml_metadata',
            'saml_metadata',
            'portal_logout',
            'portal_login_usersource_sso',
            'portal_login_callback',
            'portal_login_authenticate',
            'portal_login_submit',
        ];

        $this->setRouter($router);

        foreach ($special_routes as $route) {
            $request->getPathInfo()->willReturn('irrelevent in this test case');
            $router->match(Argument::any())->willReturn(['_route' => $route]);

            $this->isSpecialPath()->shouldReturn(true);
        }
    }
}
