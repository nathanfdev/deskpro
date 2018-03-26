<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\EventListener\Language;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Language\LanguageStack;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\EventListener\Language\LastLanguageListener
 */
class LastLanguageListenerSpec extends ObjectBehavior
{
    public function let(
        LanguageStack $language_stack,
        LoggerInterface $logger,
        FilterResponseEvent $event,
        Response $response,
        ResponseHeaderBag $headers
    ) {
        $response->headers = $headers;
        $event->getResponse()->willReturn($response);

        $this->beConstructedWith($language_stack, $logger);
    }

    public function it_does_nothing_if_there_is_no_active_language_on_the_stack(
        LanguageStack $language_stack,
        FilterResponseEvent $event,
        ResponseHeaderBag $headers
    ) {
        $language_stack->getActive()->willReturn(null);

        $headers->setCookie(Argument::any())->shouldNotBeCalled();

        $this->onKernelResponse($event);
    }

    public function it_sets_a_cookie_with_last_lang_code_if_language_exists_on_stack(
        LanguageStack $language_stack,
        FilterResponseEvent $event,
        ResponseHeaderBag $headers,
        Language $en
    ) {
        $language_stack->getActive()->willReturn($en);

        $en->getUrlCode()->willReturn('en');
        $headers->setCookie(Argument::type('Symfony\Component\HttpFoundation\Cookie'))->shouldBeCalled();

        $this->onKernelResponse($event);
    }

    public function it_subscribes_to_kernel_response()
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ]);
    }
}
