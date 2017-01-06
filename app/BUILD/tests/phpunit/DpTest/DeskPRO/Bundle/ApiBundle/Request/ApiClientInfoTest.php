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
