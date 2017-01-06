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

namespace DeskPRO\Bundle\ApiBundle\Security\EventListener;

use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Auth\AuthInterfaceSettings;
use DeskPRO\Bundle\AppBundle\Security\EventListener\SsoListener;
use DpTest\PortalTestCase;
use Symfony\Component\Debug\BufferingLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SsoListenerTest extends PortalTestCase
{
    private $authSettings;
    private $authChecker;
    private $authManager;
    private $logger;
    private $listener;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->authSettings = $this->getMockBuilder(AuthInterfaceSettings::class)->disableOriginalConstructor()->getMock();
        $this->authChecker  = $this->getMockBuilder(AuthorizationCheckerInterface::class)->disableOriginalConstructor()->getMock();
        $this->authManager  = $this->getMockBuilder(AuthenticationManager::class)->disableOriginalConstructor()->getMock();
        $this->logger       = new BufferingLogger();

        $this->authManager->method('getSettings')->willReturn($this->authSettings);

        $this->listener = $this
            ->getMockBuilder(SsoListener::class)
            ->setConstructorArgs([$this->authChecker, $this->authManager, $this->logger])
            ->setMethods(['checkAuthSystemForResponse'])
            ->getMock()
        ;
    }

    /**
     * @test
     */
    public function check_auth_is_called()
    {
        $event = new GetResponseEvent($this->getPortalKernel(), new Request(), HttpKernelInterface::MASTER_REQUEST);
        $this->listener->expects($this->once())->method('checkAuthSystemForResponse');
        $this->listener->onKernelRequest($event);
    }

    /**
     * @test
     * @dataProvider skipApiProvider
     */
    public function skip_for_portal_api()
    {
        $request = Request::create('/portal/api/tickets');
        $event   = new GetResponseEvent($this->getPortalKernel(), $request, HttpKernelInterface::MASTER_REQUEST);

        $this->listener->expects($this->never())->method('checkAuthSystemForResponse');
        $this->listener->onKernelRequest($event);
    }

    /**
     * @return array
     */
    public function skipApiProvider()
    {
        return [
            ['/portal/api/tickets/new'],
            ['/api/tickets'],
            ['/api/v2/tickets'],
        ];
    }
}
