<?php

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
