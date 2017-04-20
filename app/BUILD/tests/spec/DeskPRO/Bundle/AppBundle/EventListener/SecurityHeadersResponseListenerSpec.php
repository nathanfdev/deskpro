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

namespace spec\DeskPRO\Bundle\AppBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\IntrospectableContainerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\EventListener\SecurityHeadersResponseListener
 */
class SecurityHeadersResponseListenerSpec extends ObjectBehavior
{
    public function let(
        IntrospectableContainerInterface $container
    ) {
        $this->beConstructedWith($container);
    }

    public function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    public function it_listens_to_kernel_response_events_with_64_priority()
    {
        $this->getSubscribedEvents()->shouldBe([
            KernelEvents::RESPONSE => ['onResponse', 64],
        ]);
    }

    public function it_adds_security_headers_to_every_response(
        FilterResponseEvent $event,
        Response $response,
        Request $request,
        HeaderBag $headers
    ) {
        $response->headers = $headers;
        $event->getResponse()->willReturn($response);

        $request->isSecure()->willReturn(false);
        $request->getPathInfo()->willReturn('/');
        $event->getRequest()->willReturn($request);

        $headers->add(['X-Content-Type-Options' => 'nosniff'])->shouldBeCalled();
        $headers->add(['X-Frame-Options' => 'sameorigin'])->shouldBeCalled();
        $headers->add(['Content-Security-Policy' => 'default-src \'self\' blob:; script-src * data: \'unsafe-inline\' \'unsafe-eval\'; style-src * data: \'unsafe-inline\'; img-src * data: blob:; font-src * data:; connect-src *; media-src * data: blob:; object-src *; child-src * blob:; form-action *; frame-src *; frame-ancestors \'self\''])->shouldBeCalled();
        $headers->add(['Referrer-Policy' => 'no-referrer-when-downgrade'])->shouldBeCalled();

        $this->onResponse($event);
    }
}
