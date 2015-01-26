<?php

namespace spec\Application\PortalBundle\EventListener;

use Application\DeskPRO\HttpFoundation\Request;
use Application\PortalBundle\Mode\PortalMode;
use Application\PortalBundle\Mode\PortalModeFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PortalModeListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Application\PortalBundle\EventListener\PortalModeListener');
    }

    function let(PortalModeFactory $factory, Request $request, ParameterBag $bag)
    {
        $this->beConstructedWith($factory);
        $request->attributes = $bag;
    }

    public function it_subscribes_to_kernel_request_with_high_priority()
    {
        $this->getSubscribedEvents()->shouldBeLike(
            array(
                KernelEvents::REQUEST => array('onKernelRequest', 256)
            )
        );
    }

    public function it_passes_the_requested_path_to_the_factory(Request $request, GetResponseEvent $event, PortalModeFactory $factory)
    {
        $request->getPathInfo()->willReturn($path = '/admin-mode/en/tickets');
        $event->getRequest()->willReturn($request);

        $factory->createMode($path)->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_sets_the_mode_as_a_request_attribute(PortalMode $mode, GetResponseEvent $event, PortalModeFactory $factory, Request $request, ParameterBag $bag)
    {
        $event->getRequest()->willReturn($request);

        $factory->createMode(Argument::any())->willReturn($mode);

        $bag->set(PortalMode::ATTR_NAME, $mode)->shouldBeCalled();

        $this->onKernelRequest($event);
    }
}
