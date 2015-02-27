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

namespace spec\DeskPRO\PortalBundle\Routing;

use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\PortalBundle\Routing\PortalRequestInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\Dumper\MatcherDumperInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Routing\PortalRequestInfo
 */
class PortalRequestInfoSpec extends ObjectBehavior
{
    function let(
        Request $request,
        RouterInterface $router
    )
    {
        $this->beConstructedWith($request);
    }

    function it_finds_the_lang_code_in_the_url(
        Request $request
    )
    {
        $request->getPathInfo()->willReturn('/en/articles');
        $this->getLanguageUrlCode()->shouldReturn('en');

        $request->getPathInfo()->willReturn('/en');
        $this->getLanguageUrlCode()->shouldReturn('en');

        $request->getPathInfo()->willReturn('/en/');
        $this->getLanguageUrlCode()->shouldReturn('en');

        $request->getPathInfo()->willReturn('/articles/en');
        $this->getLanguageUrlCode()->shouldReturn(null);

        $request->getPathInfo()->willReturn('/');
        $this->getLanguageUrlCode()->shouldReturn(null);

        $request->getPathInfo()->willReturn('/admin-mode');
        $this->getLanguageUrlCode()->shouldReturn(null);
    }

    function it_finds_the_routable_path_in_the_url(
        Request $request
    )
    {
        $request->getPathInfo()->willReturn('/en/articles');
        $this->getRoutablePath()->shouldReturn('/articles');

        $request->getPathInfo()->willReturn('/en');
        $this->getRoutablePath()->shouldReturn('/');

        $request->getPathInfo()->willReturn('/en/');
        $this->getRoutablePath()->shouldReturn('/');

        $request->getPathInfo()->willReturn('/articles/en');
        $this->getRoutablePath()->shouldReturn('/articles/en');

        $request->getPathInfo()->willReturn('/');
        $this->getRoutablePath()->shouldReturn('/');

        $request->getPathInfo()->willReturn('/admin-mode');
        $this->getRoutablePath()->shouldReturn('/admin-mode');
    }

    function it_will_use_the_modes_internal_path_instead_of_the_request_pathinfo_if_given(
        Request $request,
        PortalMode $mode
    )
    {
        $request->getPathInfo()->willReturn('/admin-mode/en/articles');
        $mode->getInternalPath()->willReturn('/en/articles');

        $this->beConstructedWith($request, $mode);

        $this->getLanguageUrlCode()->shouldReturn('en');
        $this->getRoutablePath()->shouldReturn('/articles');


        $mode->getInternalPath()->willReturn('/en/');
        $this->getLanguageUrlCode()->shouldReturn('en');
        $this->getRoutablePath()->shouldReturn('/');

        $mode->getInternalPath()->willReturn('/articles');
        $this->getLanguageUrlCode()->shouldReturn(null);
        $this->getRoutablePath()->shouldReturn('/articles');
    }

    function it_will_report_a_proxy_url_as_special(
        Request $request
    )
    {
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

    function it_will_report_any_url_starting_with_underscore_as_special(
        Request $request
    )
    {
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

    function it_uses_the_router_to_see_if_a_url_is_of_a_special_route(
        Request $request,
        RouterInterface $router
    )
    {
        $special_routes = array(
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
            'portal_login_submit'
        );

        $this->setRouter($router);

        foreach ($special_routes as $route) {
            $request->getPathInfo()->willReturn('irrelevent in this test case');
            $router->match(Argument::any())->willReturn(array('_route' => $route));

            $this->isSpecialPath()->shouldReturn(true);
        }
    }
}
