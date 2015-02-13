<?php

namespace spec\Application\PortalBundle\EventListener;

use League\Url\Components\Port;
use Symfony\Component\HttpFoundation\Request;
use Application\PortalBundle\Mode\PortalMode;
use Application\PortalBundle\Mode\PortalModeFactory;
use Application\PortalBundle\Mode\PortalModeStorage;
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
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function let(PortalModeFactory $factory, PortalModeStorage $store)
    {
        $this->beConstructedWith($factory, $store);
    }

    public function it_subscribes_to_kernel_request_with_high_priority()
    {
        $this->getSubscribedEvents()->shouldBeLike(
            array(
                KernelEvents::REQUEST => array('onKernelRequest', 256)
            )
        );
    }

    public function it_creates_and_saves_the_mode_to_mode_storage_on_master_request(
        PortalMode $mode,
        GetResponseEvent $event,
        PortalModeFactory $factory,
        Request $request,
        PortalModeStorage $store
    )
    {
        $request->getPathInfo()->willReturn($path = '/admin-mode/en/tickets');

        $event->isMasterRequest()->willReturn(true);
        $event->getRequest()->willReturn($request);

        $factory->createMode($path)->willReturn($mode);

        $store->setMode($mode)->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_ignores_sub_requests(
        GetResponseEvent $event,
        PortalModeFactory $factory,
        PortalModeStorage $store
    )
    {
        $event->isMasterRequest()->willReturn(false);

        $factory->createMode(Argument::any())->shouldNotBeCalled();
        $store->setMode(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }
}
