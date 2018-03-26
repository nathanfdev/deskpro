<?php

namespace DeskPRO\Bundle\ApiBundle\Security\EventListener;

use Application\DeskPRO\Auth\AuthInterfaceSettings;
use Application\DeskPRO\Auth\AuthSettings;
use DeskPRO\Bundle\AppBundle\Security\EventListener\SsoListener;
use DpTest\PortalTestCase;
use Symfony\Component\Debug\BufferingLogger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class SsoListenerTest.
 */
class SsoListenerTest extends PortalTestCase
{
    private $authSettings;
    private $authChecker;
    private $container;
    private $logger;
    private $listener;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->authSettings = $this->getMockBuilder(AuthInterfaceSettings::class)->disableOriginalConstructor()->getMock();
        $this->authChecker  = $this->getMockBuilder(AuthorizationCheckerInterface::class)->disableOriginalConstructor()->getMock();
        $this->authSettings = $this->getMockBuilder(AuthSettings::class)->disableOriginalConstructor()->getMock();
        $this->container    = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $this->logger       = new BufferingLogger();

        $this->container->method('get')->willReturn($this->authSettings);
        $this->authSettings->method('getUserInterfaceSettings')->willReturn($this->getMockBuilder(AuthInterfaceSettings::class)->disableOriginalConstructor()->getMock());

        $this->listener = $this
            ->getMockBuilder(SsoListener::class)
            ->setConstructorArgs([$this->authChecker, $this->container, $this->logger])
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
