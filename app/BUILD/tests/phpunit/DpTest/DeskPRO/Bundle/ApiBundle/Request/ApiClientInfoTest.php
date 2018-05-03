<?php

namespace DpTest\DeskPRO\Bundle\ApiBundle\Request;

use DeskPRO\Bundle\ApiBundle\Request\ApiClientInfo;
use DpTest\DeskProTestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiClientInfoTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_return_standard_when_unspecified()
    {
        $requestStack     = \Mockery::mock(RequestStack::class);
        $request          = \Mockery::mock(Request::class);
        $request->headers = new HeaderBag();

        $requestStack->shouldReceive('getMasterRequest')->andReturn($request);

        $apiClientInfo = ApiClientInfo::createFromRequestStack($requestStack);

        $this->assertEquals('standard', $apiClientInfo->getClientType());
        $this->assertEquals('0', $apiClientInfo->getClientVersion());
        $this->assertFalse($apiClientInfo->isIos());
    }

    /**
     * @test
     */
    public function it_should_return_ios_with_iosheader()
    {
        $requestStack     = \Mockery::mock(RequestStack::class);
        $request          = \Mockery::mock(Request::class);
        $request->headers = new HeaderBag();
        $request->headers->set('X-DeskPRO-API-ClientType', 'ios (v123)');

        $requestStack->shouldReceive('getMasterRequest')->andReturn($request);

        $apiClientInfo = ApiClientInfo::createFromRequestStack($requestStack);

        $this->assertEquals('ios', $apiClientInfo->getClientType());
        $this->assertEquals('123', $apiClientInfo->getClientVersion());
        $this->assertTrue($apiClientInfo->isIos());
    }

    /**
     * @test
     */
    public function it_should_return_proper_client_and_version()
    {
        $requestStack     = \Mockery::mock(RequestStack::class);
        $request          = \Mockery::mock(Request::class);
        $request->headers = new HeaderBag();
        $request->headers->set('X-DeskPRO-API-ClientType', 'foo (v55)');

        $requestStack->shouldReceive('getMasterRequest')->andReturn($request);

        $apiClientInfo = ApiClientInfo::createFromRequestStack($requestStack);

        $this->assertEquals('foo', $apiClientInfo->getClientType());
        $this->assertEquals('55', $apiClientInfo->getClientVersion());
        $this->assertFalse($apiClientInfo->isIos());
    }

    /**
     * @test
     */
    public function it_should_return_proper_client()
    {
        $requestStack     = \Mockery::mock(RequestStack::class);
        $request          = \Mockery::mock(Request::class);
        $request->headers = new HeaderBag();
        $request->headers->set('X-DeskPRO-API-ClientType', 'foo');

        $requestStack->shouldReceive('getMasterRequest')->andReturn($request);

        $apiClientInfo = ApiClientInfo::createFromRequestStack($requestStack);

        $this->assertEquals('foo', $apiClientInfo->getClientType());
        $this->assertEquals('0', $apiClientInfo->getClientVersion());
        $this->assertFalse($apiClientInfo->isIos());
    }

    public function it_should_modify_header_to_lower_case()
    {
        $requestStack     = \Mockery::mock(RequestStack::class);
        $request          = \Mockery::mock(Request::class);
        $request->headers = new HeaderBag();
        $request->headers->set('X-DeskPRO-API-ClientType', 'iOS (v1.0.92)');

        $apiClientInfo = ApiClientInfo::createFromRequestStack($requestStack);
        $this->assertEquals('ios', $apiClientInfo->getClientType());
        $this->assertEquals('1.0.92', $apiClientInfo->getClientVersion());
        $this->assertTrue($apiClientInfo->isIos());
    }
}
