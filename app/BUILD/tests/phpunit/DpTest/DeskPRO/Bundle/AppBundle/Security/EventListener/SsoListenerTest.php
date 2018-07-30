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

    public function test_check_auth_is_called()
    {
        $event = new GetResponseEvent($this->getPortalKernel(), new Request(), HttpKernelInterface::MASTER_REQUEST);
        $this->listener->expects($this->once())->method('checkAuthSystemForResponse');
        $this->listener->onKernelRequest($event);
    }

    /**
     * @dataProvider skipPortalApiProvider
     *
     * @param string $url
     */
    public function test_skip_for_portal_api($url)
    {
        $request = Request::create($url, 'GET');
        $request->attributes->add($this->getContainer()->get('router')->match($url));

        $event = new GetResponseEvent($this->getPortalKernel(), $request, HttpKernelInterface::MASTER_REQUEST);

        $this->listener->expects($this->never())->method('checkAuthSystemForResponse');
        $this->listener->onKernelRequest($event);
    }

    /**
     * @dataProvider skipApiProvider
     *
     * @param string $url
     */
    public function test_skip_for_api($url)
    {
        $request = Request::create($url, 'GET');

        $event = new GetResponseEvent($this->getPortalKernel(), $request, HttpKernelInterface::MASTER_REQUEST);

        $this->listener->expects($this->never())->method('checkAuthSystemForResponse');
        $this->listener->onKernelRequest($event);
    }

    /**
     * @return array
     */
    public function skipPortalApiProvider()
    {
        return [
            ['/portal/api/tickets/new'],
        ];
    }

    /**
     * @return array
     */
    public function skipApiProvider()
    {
        return [
            ['/api/tickets'],
            ['/api/v2/tickets'],
        ];
    }
}
