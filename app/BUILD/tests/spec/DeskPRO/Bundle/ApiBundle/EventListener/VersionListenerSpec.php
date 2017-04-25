<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace spec\DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Request\ApiVersionInfo;
use DeskPRO\Bundle\AppBundle\Routing\RequestMatcher;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @mixin \DeskPRO\Bundle\ApiBundle\EventListener\VersionListener
 */
class VersionListenerSpec extends ObjectBehavior
{
    public function let(
        ApiVersionInfo $versionInfo,
        RequestMatcher $requestMatcher
    ) {
        $this->beConstructedWith($versionInfo, $requestMatcher);
    }

    public function it_gets_closes_version(
        Request          $request,
        GetResponseEvent $event,
        ApiVersionInfo   $versionInfo,
        ParameterBag     $attributes,
        RequestMatcher   $requestMatcher
    ) {
        $event->getRequest()->willReturn($request);
        $request->getPathInfo()->willReturn('/api/v2/1234/people');
        $request->getMethod()->willReturn('GET');

        $versionInfo->getClosestVersion('1234')->willReturn('20160101');
        $versionInfo->getDefaultVersion()->shouldBeCalled();
        $versionInfo->getDefaultVersion()->willReturn('20170401');
        $versionInfo->getLowerVersions('20160101')->willReturn([
            '20160101',
        ]);

        $attributes->set('version', '20160101')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/20160101/people', 'GET')->shouldBeCalled();
        $requestMatcher->routeExists('/api/v2/20160101/people', 'GET')->willReturn(true);

        $this->onRequest($event);
    }

    public function it_uses_default_version(
        Request          $request,
        GetResponseEvent $event,
        ApiVersionInfo   $versionInfo,
        ParameterBag     $attributes,
        RequestMatcher   $requestMatcher
    ) {
        $event->getRequest()->willReturn($request);
        $request->getPathInfo()->willReturn('/api/v2/people');
        $request->getMethod()->willReturn('GET');

        $versionInfo->getDefaultVersion()->shouldBeCalled();
        $versionInfo->getDefaultVersion()->willReturn('20170401');
        $versionInfo->getLowerVersions('20170401')->willReturn([
            '20170401',
            '20160101',
        ]);

        $attributes->set('version', '20170401')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/20170401/people', 'GET')->shouldNotBeCalled();
        $requestMatcher->routeExists('/api/v2/20160101/people', 'GET')->shouldNotBeCalled();
        $requestMatcher->routeExists('/api/v2/people', 'GET')->willReturn(true);

        $this->onRequest($event);
    }

    public function it_uses_concrete_version(
        Request          $request,
        GetResponseEvent $event,
        ApiVersionInfo   $versionInfo,
        ParameterBag     $attributes,
        RequestMatcher   $requestMatcher
    ) {
        $event->getRequest()->willReturn($request);
        $request->getPathInfo()->willReturn('/api/v2/20170401/people');
        $request->getMethod()->willReturn('GET');

        $versionInfo->getDefaultVersion()->willReturn('20170401');
        $versionInfo->getClosestVersion('20170401')->willReturn('20170401');
        $versionInfo->getLowerVersions('20170401')->willReturn([
            '20170401',
            '20160101',
        ]);

        $attributes->set('version', '20170401')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/people', 'GET')->shouldBeCalled();
        $requestMatcher->routeExists('/api/v2/people', 'GET')->willReturn(true);

        $this->onRequest($event);
    }

    public function it_uses_concrete_version_fallback(
        Request          $request,
        GetResponseEvent $event,
        ApiVersionInfo   $versionInfo,
        ParameterBag     $attributes,
        RequestMatcher   $requestMatcher
    ) {
        $event->getRequest()->willReturn($request);
        $request->getPathInfo()->willReturn('/api/v2/20160101/people');
        $request->getMethod()->willReturn('GET');

        $versionInfo->getDefaultVersion()->willReturn('20170401');
        $versionInfo->getClosestVersion('20160101')->willReturn('20160101');
        $versionInfo->getLowerVersions('20160101')->willReturn([
            '20160101',
        ]);

        $attributes->set('version', '20160101')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/20170401/people', 'GET')->shouldNotBeCalled();
        $requestMatcher->routeExists('/api/v2/20160101/people', 'GET')->willReturn(false);
        $requestMatcher->routeExists('/api/v2/people', 'GET')->willReturn(true);

        $this->onRequest($event);
    }
}
