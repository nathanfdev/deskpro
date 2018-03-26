<?php

namespace DpTest\DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Helper\UrlHostChecker;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener;
use DpTest\PortalTestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RedirectProtectionListenerTest extends PortalTestCase
{
    /**
     * @test
     */
    public function real_cloud_example()
    {
        $listener = $this->createListener('http://custom-domain.co.uk');
        $event    = $this->createEvent('https://example.deskpro.com/some-page/here', 'http://custom-domain.co.uk/foo/bar');

        $listener->onResponse($event);
        $this->assertFalse($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_allow_redirect_to_self()
    {
        $listener = $this->createListener('http://other-domain.com');
        $event    = $this->createEvent('http://domain-a.com/baz', 'http://domain-a.com/foo/bar');

        $listener->onResponse($event);
        $this->assertFalse($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_allow_redirect_to_brand()
    {
        $listener = $this->createListener('http://other-domain.com');
        $event    = $this->createEvent('http://domain-a.com/baz', 'http://other-domain.com/foo/bar');

        $listener->onResponse($event);
        $this->assertFalse($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_allow_redirect_to_brand_scheme_change()
    {
        $listener = $this->createListener('https://other-domain.com');
        $event    = $this->createEvent('http://domain-a.com/baz', 'https://other-domain.com/foo/bar');

        $listener->onResponse($event);
        $this->assertFalse($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_allow_redirect_to_self_scheme_change()
    {
        $listener = $this->createListener('http://other-domain.com');
        $event    = $this->createEvent('http://domain-a.com/baz', 'https://domain-a.com/foo/bar');

        $listener->onResponse($event);
        $this->assertFalse($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_not_allow_redirect_off()
    {
        $listener = $this->createListener('http://other-domain.com');
        $event    = $this->createEvent('http://domain-a.com/baz', 'http://bad.com/foo/bar');

        $listener->onResponse($event);
        $this->assertTrue($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_not_allow_arbitrary_port_change()
    {
        $listener = $this->createListener('http://other-domain.com');
        $event    = $this->createEvent('http://domain-a.com:8080/baz', 'https://domain-a.com/foo/bar');

        $listener->onResponse($event);
        $this->assertTrue($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_not_allow_arbitrary_port_change_2()
    {
        $listener = $this->createListener('http://other-domain.com');
        $event    = $this->createEvent('http://domain-a.com/baz', 'https://domain-a.com:8080/foo/bar');

        $listener->onResponse($event);
        $this->assertTrue($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_not_allow_arbitrary_port_change_in_brand()
    {
        $listener = $this->createListener('http://other-domain.com');
        $event    = $this->createEvent('http://domain-a.com/baz', 'https://other-domain.com:8080/foo/bar');

        $listener->onResponse($event);
        $this->assertTrue($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function it_should_not_allow_arbitrary_port_change_in_brand_2()
    {
        $listener = $this->createListener('http://other-domain.com:8080');
        $event    = $this->createEvent('http://domain-a.com/baz', 'https://other-domain.com/foo/bar');

        $listener->onResponse($event);
        $this->assertTrue($event->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN);
    }

    /**
     * @param string $brandUrl
     *
     * @return RedirectProtectionListener
     */
    private function createListener($brandUrl)
    {
        $brandStack = \Mockery::mock(BrandStack::class);
        $brand      = \Mockery::mock(BrandContainer::class);

        $brandStack->shouldReceive('getActive')->andReturn($brand);
        $brand->shouldReceive('getSetting')->with('core.deskpro_url', '')->andReturn($brandUrl);

        $hostChecker = new UrlHostChecker();
        $logger      = new NullLogger();

        $listener = new RedirectProtectionListener($brandStack, $hostChecker, $logger);

        return $listener;
    }

    /**
     * @param $currentUrl
     * @param $redirectUrl
     *
     * @return FilterResponseEvent
     */
    private function createEvent($currentUrl, $redirectUrl)
    {
        $request = Request::create($currentUrl, 'GET', [], [], [], [
            'SCRIPT_NAME'     => 'index.php',
            'SCRIPT_FILENAME' => 'index.php',
        ]);

        $response = new RedirectResponse($redirectUrl);

        return new FilterResponseEvent($this->getPortalKernel(), $request, HttpKernelInterface::MASTER_REQUEST, $response);
    }
}
