<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace spec\DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Request\UrlCorrector;
use DeskPRO\Bundle\AppBundle\Request\UrlCorrectorFactory;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Routing\RedirectToUrlException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\EventListener\RedirectToUrlExceptionListener
 */
class RedirectToUrlExceptionListenerSpec extends ObjectBehavior
{
    public function let(
        LoggerInterface $logger,
        UrlCorrectorFactory $urlCorrectorFactory,
        UrlCorrector $urlCorrector,
        BrandStack $brandStack,
        Brand $brand,
        BrandContainer $brandContainer
    ) {
        $this->beConstructedWith($logger, $urlCorrectorFactory, $brandStack);
        $brandStack->getActive()->willReturn($brandContainer);
        $brandContainer->getBrand()->willReturn($brand);
        $urlCorrectorFactory->createUrlCorrector($brand)->willReturn($urlCorrector);
    }

    public function it_subscrbied_to_kernel_exceptions()
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::EXCEPTION => ['onKernelException', 129],
        ]);
    }

    public function it_does_nothing_if_wrong_exception(
        \RuntimeException $wrong_exception,
        GetResponseForExceptionEvent $event,
        Request $request
    ) {
        $event->getException()->willReturn($wrong_exception);
        $event->getRequest()->willReturn($request);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelException($event);
    }

    public function it_sets_a_redirect_response_if_it_is_the_right_exception(
        RedirectToUrlException $correct_exception,
        GetResponseForExceptionEvent $event,
        Request $request
    ) {
        $event->getException()->willReturn($correct_exception);
        $event->getRequest()->willReturn($request);

        $correct_exception->getUrl()->shouldBeCalled()->willReturn('http://redirect.here');
        $event->setResponse(Argument::type('Symfony\Component\HttpFoundation\Response'))->shouldBeCalled();
        $event->stopPropagation()->shouldBeCalled();

        $this->onKernelException($event);
    }
}
