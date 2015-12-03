<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
        $this->getSubscribedEvents()->shouldReturn(array(
            KernelEvents::RESPONSE => array('onKernelResponse'),
        ));
    }
}
