<?php

namespace spec\DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PortalModeListenerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('DeskPRO\Bundle\PortalBundle\EventListener\PortalModeListener');
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    public function let(PortalModeFactory $factory, PortalModeStorage $store, LoggerInterface $logger)
    {
        $this->beConstructedWith($factory, $store, $logger);
    }

    public function it_subscribes_to_kernel_request_with_high_priority()
    {
        $this->getSubscribedEvents()->shouldBeLike(
            [
                KernelEvents::REQUEST => ['onKernelRequest', 513],
            ]
        );
    }

    public function it_creates_and_saves_the_mode_to_mode_storage_on_master_request(
        PortalMode $mode,
        GetResponseEvent $event,
        PortalModeFactory $factory,
        Request $request,
        PortalModeStorage $store,
        AttributeBag $attr_bag
    ) {
        $request->getPathInfo()->willReturn($path = '/admin-mode/en/tickets');
        $request->attributes = $attr_bag;

        $event->isMasterRequest()->willReturn(true);
        $event->getRequest()->willReturn($request);

        $factory->createMode($path)->willReturn($mode);

        $store->setMode($mode)->shouldBeCalled();
        $mode->__toString()->willReturn('admin');

        $this->onKernelRequest($event);
    }

    public function it_ignores_sub_requests(
        GetResponseEvent $event,
        PortalModeFactory $factory,
        PortalModeStorage $store,
        Request $request,
        AttributeBag $attr_bag
    ) {
        $event->isMasterRequest()->willReturn(false);
        $request->attributes = $attr_bag;
        $event->getRequest()->willReturn($request);

        $factory->createMode(Argument::any())->shouldNotBeCalled();
        $store->setMode(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }
}
