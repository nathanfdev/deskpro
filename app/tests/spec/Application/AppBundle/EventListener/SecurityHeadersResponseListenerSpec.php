<?php

namespace spec\Application\AppBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @mixin \Application\AppBundle\EventListener\SecurityHeadersResponseListener
 */
class SecurityHeadersResponseListenerSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_listens_to_kernel_response_events_with_64_priority()
    {
        $this->getSubscribedEvents()->shouldBe(
            array(
                KernelEvents::RESPONSE => array('onResponse', 64)
            )
        );
    }

    function it_adds_security_headers_to_every_response(
        FilterResponseEvent $event,
        Response $response,
        HeaderBag $headers
    )
    {
        $response->headers = $headers;
        $event->getResponse()->willReturn($response);

        $headers->add(
            array(
                'X-Content-Type-Options' => 'nosniff',
                'X-FRAME-OPTIONS' => 'DENY'
            )
        )->shouldBeCalled();

        $this->onResponse($event);
    }
}
