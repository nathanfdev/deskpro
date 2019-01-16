<?php

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

    public function it_gets_closest_version(
        Request          $request,
        GetResponseEvent $event,
        ApiVersionInfo   $versionInfo,
        ParameterBag     $attributes,
        RequestMatcher   $requestMatcher
    ) {
        $event->getRequest()->willReturn($request);
        $request->getPathInfo()->willReturn('/api/v2/20160102/people');
        $request->getMethod()->willReturn('GET');

        $versionInfo->getLowerVersions('20160102')->willReturn([
            '20160101',
        ]);

        $versionInfo->getNextVersions('20160102')->willReturn([
            '20160110',
        ]);

        $attributes->set('version', '20160102')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/20160110/people', 'GET')->shouldBeCalled();
        $requestMatcher->routeExists('/api/v2/20160110/people', 'GET')->willReturn(true);

        $this->onRequest($event);
    }

    public function it_gets_closest_max_version(
        Request          $request,
        GetResponseEvent $event,
        ApiVersionInfo   $versionInfo,
        ParameterBag     $attributes,
        RequestMatcher   $requestMatcher
    ) {
        $event->getRequest()->willReturn($request);
        $request->getPathInfo()->willReturn('/api/v2/20160102/people');
        $request->getMethod()->willReturn('GET');

        $versionInfo->getLowerVersions('20160102')->willReturn([
            '20160101',
        ]);

        $versionInfo->getNextVersions('20160102')->willReturn([]);

        $attributes->set('version', '20160102')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/people', 'GET')->shouldBeCalled();
        $requestMatcher->routeExists('/api/v2/people', 'GET')->willReturn(true);

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
        $versionInfo->getDefaultVersion()->willReturn('20170501');
        $versionInfo->getLowerVersions('20170501')->willReturn([
            '20170401',
            '20160101',
        ]);
        $versionInfo->getNextVersions('20170501')->willReturn([]);

        $attributes->set('version', '20170501')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/20170401/people', 'GET')->shouldNotBeCalled();
        $requestMatcher->routeExists('/api/v2/20160101/people', 'GET')->shouldNotBeCalled();
        $requestMatcher->routeExists('/api/v2/people', 'GET')->willReturn(true);

        $this->onRequest($event);
    }

    public function it_uses_default_max_version(
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
        $versionInfo->getDefaultVersion()->willReturn('20170501');
        $versionInfo->getLowerVersions('20170501')->willReturn([
            '20170401',
            '20160101',
        ]);
        $versionInfo->getNextVersions('20170501')->willReturn([]);

        $attributes->set('version', '20170501')->shouldBeCalled();
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

        $versionInfo->getLowerVersions('20170401')->willReturn([
            '20170401',
            '20160101',
        ]);
        $versionInfo->getNextVersions('20170401')->willReturn([]);

        $attributes->set('version', '20170401')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/20170401/people', 'GET')->shouldBeCalled();
        $requestMatcher->routeExists('/api/v2/20170401/people', 'GET')->willReturn(true);

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

        $versionInfo->getLowerVersions('20160101')->willReturn([
            '20160101',
        ]);
        $versionInfo->getNextVersions('20160101')->willReturn([
            '20170401',
        ]);

        $attributes->set('version', '20160101')->shouldBeCalled();
        $request->attributes = $attributes;

        $requestMatcher->routeExists('/api/v2/20170401/people', 'GET')->shouldNotBeCalled();
        $requestMatcher->routeExists('/api/v2/20160101/people', 'GET')->willReturn(false);
        $requestMatcher->routeExists('/api/v2/people', 'GET')->willReturn(true);

        $this->onRequest($event);
    }
}
