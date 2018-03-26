<?php

namespace spec\DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Helper\UrlHostChecker;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RedirectProtectionListenerSpec extends ObjectBehavior
{
    public function let(
        BrandStack $brand_stack,
        BrandContainer $brand_container,
        LoggerInterface $logger,
        FilterResponseEvent $event,
        Response $response,
        Request $request,
        HeaderBag $response_headers,
        UrlHostChecker $url_host_checker
    ) {
        $brand_stack->getActive()->willReturn($brand_container);
        $event->getResponse()->willReturn($response);
        $event->getRequest()->willReturn($request);

        $response->headers = $response_headers;

        $this->beConstructedWith($brand_stack, $url_host_checker, $logger);
    }

    public function it_does_not_change_response_if_not_redirect(
        Response $response,
        FilterResponseEvent $event,
        Request $request,
        AttributeBag $attr_bag
    ) {
        $response->isRedirect()->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $event->getRequest()->willReturn($request);
        $request->attributes = $attr_bag;

        $this->onResponse($event);
    }

    public function it_does_not_change_response_if_special_header_exists_and_it_removes_the_special_header(
        Response $response,
        Request $request,
        AttributeBagInterface $attr_bag,
        HeaderBag $response_headers,
        FilterResponseEvent $event
    ) {
        $request->attributes = $attr_bag;
        $event->getRequest()->willReturn($request);
        $response->isRedirect()->willReturn(true);

        $response_headers
            ->get(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, Argument::type('bool'))
            ->willReturn('something truthy');

        $response_headers
            ->remove(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER)
            ->shouldBeCalled();

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onResponse($event);
    }

    public function it_will_change_response_if_redirect_url_does_not_match_config(
        UrlHostChecker $url_host_checker,
        Response $response,
        HeaderBag $response_headers,
        FilterResponseEvent $event,
        Request $request,
        AttributeBagInterface $attr_bag
    ) {
        $request->attributes = $attr_bag;
        $event->getRequest()->willReturn($request);
        $response->isRedirect()->willReturn(true);

        $response_headers
            ->get(
                \DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER,
                Argument::type('bool')
            )
            ->willReturn(null);

        $response_headers->get('Location')->willReturn('http://invalid.com');

        $request->getPort()->willReturn(80);
        $request->getHost()->willReturn('valid-site.com');

        // both url matcher checks return false here, because they are dummies

        $event->setResponse(Argument::any())->shouldBeCalled();

        $this->onResponse($event);
    }

    public function it_will_not_change_response_if_redirect_url_matches_current_request(
        UrlHostChecker $url_host_checker,
        Response $response,
        HeaderBag $response_headers,
        FilterResponseEvent $event,
        Request $request,
        AttributeBagInterface $attr_bag
    ) {
        $request->attributes = $attr_bag;
        $event->getRequest()->willReturn($request);
        $response->isRedirect()->willReturn(true);

        $response_headers
            ->get(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, Argument::type('bool'))
            ->willReturn(null);

        $location = 'http://valid-site.com';
        $response_headers->get('Location')->willReturn($location);

        $request->getPort()->willReturn(80);
        $request_url = 'valid-site.com';
        $request->getHost()->willReturn($request_url);

        $url_host_checker->isMatch($location, $request_url, 80)->willReturn(true);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onResponse($event);
    }

    public function it_will_set_response_location_same_as_in_request_if_redirect_url_does_not_match_current_request_but_matches_brand_settings(
        UrlHostChecker $url_host_checker,
        Response $response,
        HeaderBag $response_headers,
        FilterResponseEvent $event,
        Request $request,
        BrandContainer $brand_container,
        AttributeBagInterface $attr_bag
    ) {
        $request->attributes = $attr_bag;
        $event->getRequest()->willReturn($request);
        $response->isRedirect()->willReturn(true);

        $response_headers
            ->get(
                \DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER,
                Argument::type('bool')
            )
            ->willReturn(null);

        $location = 'http://check.com:8888/some/path';
        $response_headers->get('Location')->willReturn($location);

        $request->getScheme()->willReturn('http');
        $request->getPort()->willReturn(80);
        $request_url = 'valid-site.com';
        $request->getHost()->willReturn($request_url);

        $url_host_checker->isMatch($location, $request_url, 80)->willReturn(false);

        $valid_settings_url = 'http://check.com:8888';
        $brand_container->getSetting('core.deskpro_url', null)->willReturn($valid_settings_url);
        $url_host_checker->isMatchUrl($location, $valid_settings_url)->willReturn(true);

        $response_headers->set('Location', Argument::any())->shouldBeCalled();
        $response->getContent()->shouldBeCalled();
        $response->setContent(Argument::any())->shouldBeCalled();

        $this->onResponse($event);
    }
}
