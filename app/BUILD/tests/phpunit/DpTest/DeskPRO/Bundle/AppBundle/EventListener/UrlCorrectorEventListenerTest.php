<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\EventListener\UrlCorrectorEventListener;
use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DpTest\PortalTestCase;
use Symfony\Component\Debug\BufferingLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class UrlCorrectorEventListenerTest.
 */
class UrlCorrectorEventListenerTest extends PortalTestCase
{
    /**
     * @var InterfaceInfo
     */
    private $interfaceInfo;

    /**
     * @var BufferingLogger
     */
    private $logger;

    /**
     * @var Request
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->interfaceInfo = new InterfaceInfo(InterfaceInfo::ID_USER);
        $this->logger        = new BufferingLogger();
    }

    /**
     * @test
     */
    public function redirect_response()
    {
        $event = $this->createEvent('http://example.com/index.php/new-ticket');

        $listener = $this->createListener($event);
        $listener->onController($event);

        $controller = $event->getController();
        $this->assertInstanceOf(RedirectResponse::class, $controller());
    }

    /**
     * @param string $apiUrl
     *
     * @test
     * @dataProvider skipApiProvider
     */
    public function skip_portal_api($apiUrl)
    {
        $event = $this->createEvent('http://example.com/index.php'.$apiUrl);

        $listener = $this->createListener($event);
        $listener->onController($event);

        $controller = $event->getController();
        $this->assertEquals('testController', $controller());
        $this->assertSkipInfo('[UrlCorrector] Skip: Ignore API requests');
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

    /**
     * @param FilterControllerEvent $event
     *
     * @return UrlCorrectorEventListener
     */
    private function createListener(FilterControllerEvent $event)
    {
        $portalModeFactory = new PortalModeFactory();
        $portalModeStorage = new PortalModeStorage();
        $portalModeStorage->setMode($portalModeFactory->createMode($event->getRequest()->getPathInfo()));

        return new UrlCorrectorEventListener(
            $this->interfaceInfo,
            $this->getContainer()->get('brand_stack'),
            $portalModeStorage,
            $this->getContainer()->get('url_corrector_factory'),
            $this->logger
        );
    }

    /**
     * @param string $uri
     *
     * @return FilterControllerEvent
     */
    private function createEvent($uri)
    {
        $this->request = Request::create($uri, 'GET', [], [], [], [
            'SCRIPT_NAME'     => 'index.php',
            'SCRIPT_FILENAME' => 'index.php',
        ]);

        $controller = function () {
            return 'testController';
        };

        return new FilterControllerEvent(
            $this->getPortalKernel(),
            $controller,
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * @param string $expected
     */
    private function assertSkipInfo($expected)
    {
        $log = current($this->logger->cleanLogs());
        $this->assertEquals($expected, $log[1]);
    }
}
