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
