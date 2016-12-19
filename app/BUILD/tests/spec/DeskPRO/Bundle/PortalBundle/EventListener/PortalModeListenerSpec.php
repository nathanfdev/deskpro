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
