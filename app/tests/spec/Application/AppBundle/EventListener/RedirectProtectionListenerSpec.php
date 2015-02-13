<?php

namespace spec\Application\AppBundle\EventListener;

use Application\AppBundle\EventListener\RedirectProtectionListener;
use Application\AppBundle\Helper\UrlHostChecker;
use Application\DeskPRO\Brand\BrandContainer;
use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\NewSettings\SettingsBag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class RedirectProtectionListenerSpec extends ObjectBehavior
{
    function let(
        BrandStack $brand_stack,
        BrandContainer $brand_container,
        LoggerInterface $logger,
        FilterResponseEvent $event,
        Response $response,
        Request $request,
        HeaderBag $response_headers,
        UrlHostChecker $url_host_checker
    )
    {
        $brand_stack->getActive()->willReturn($brand_container);
        $event->getResponse()->willReturn($response);
        $event->getRequest()->willReturn($request);

        $response->headers = $response_headers;

        $this->beConstructedWith($brand_stack, $url_host_checker, $logger);
    }

    function it_does_not_change_response_if_not_redirect(
        Response $response,
        FilterResponseEvent $event
    )
    {
        $response->isRedirection()->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onResponse($event);
    }

    function it_does_not_change_response_if_special_header_exists_and_it_removes_the_special_header(
        Response $response,
        HeaderBag $response_headers,
        FilterResponseEvent $event
    )
    {
        $response->isRedirection()->willReturn(true);

        $response_headers
            ->get(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, Argument::type('bool'))
            ->willReturn('something truthy')
        ;

        $response_headers
            ->remove(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER)
            ->shouldBeCalled()
        ;

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onResponse($event);
    }

    function it_will_change_response_if_redirect_url_does_not_match_config(
        UrlHostChecker $url_host_checker,
        Response $response,
        HeaderBag $response_headers,
        FilterResponseEvent $event,
        Request $request
    )
    {
        $response->isRedirection()->willReturn(true);

        $response_headers
            ->get(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, Argument::type('bool'))
            ->willReturn(null)
        ;

        $response_headers->get('Location')->willReturn('http://invalid.com');

        $request->getPort()->willReturn(80);
        $request->getHost()->willReturn('valid-site.com');

        // both url matcher checks return false here, because they are dummies

        $event->setResponse(Argument::any())->shouldBeCalled();

        $this->onResponse($event);
    }

    function it_will_not_change_response_if_redirect_url_matches_current_request(
        UrlHostChecker $url_host_checker,
        Response $response,
        HeaderBag $response_headers,
        FilterResponseEvent $event,
        Request $request
    )
    {
        $response->isRedirection()->willReturn(true);

        $response_headers
            ->get(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, Argument::type('bool'))
            ->willReturn(null)
        ;

        $location = 'http://valid-site.com';
        $response_headers->get('Location')->willReturn($location);

        $request->getPort()->willReturn(80);
        $request_url = 'valid-site.com';
        $request->getHost()->willReturn($request_url);

        $url_host_checker->isMatch($location, $request_url, 80)->willReturn(true);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onResponse($event);
    }

    function it_will_not_change_response_if_redirect_url_does_not_match_current_request_but_matches_brand_settings(
        UrlHostChecker $url_host_checker,
        Response $response,
        HeaderBag $response_headers,
        FilterResponseEvent $event,
        Request $request,
        BrandContainer $brand_container
    )
    {
        $response->isRedirection()->willReturn(true);

        $response_headers
            ->get(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, Argument::type('bool'))
            ->willReturn(null)
        ;

        $location = 'http://check.com:8888/some/path';
        $response_headers->get('Location')->willReturn($location);

        $request->getPort()->willReturn(80);
        $request_url = 'valid-site.com';
        $request->getHost()->willReturn($request_url);

        $url_host_checker->isMatch($location, $request_url, 80)->willReturn(false);

        $valid_settings_url = 'http://check.com:8888';
        $brand_container->getSetting('core.deskpro_url', null)->willReturn($valid_settings_url);
        $url_host_checker->isMatchUrl($location, $valid_settings_url)->willReturn(true);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onResponse($event);
    }
}
