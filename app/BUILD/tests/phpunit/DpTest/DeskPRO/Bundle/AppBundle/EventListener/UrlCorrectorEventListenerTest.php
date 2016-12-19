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
     * @test
     */
    public function focus_window_mode_attr()
    {
        $event = $this->createEvent('http://example.com/index.php/focus-win/new-ticket');

        $listener = $this->createListener($event);
        $listener->onController($event);

        $controller = $event->getController();
        $this->assertEquals('testController', $controller());
        $this->assertEquals(['index_segment', 'host'], $this->request->attributes->get('deskpro.url_corrector.corrections'));
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
