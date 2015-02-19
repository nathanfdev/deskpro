<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace DpUnitTests\PortalBundle\Visitor;

use Application\PortalBundle\Visitor\VisitorIdentificationProvider;
use Psr\Log\NullLogger;

class VisitorIdentificationProviderTest extends \DpUnitTestCase
{
    public function testGetIdentifierEvenIfNoCookie()
    {
        $request_stack_mock = \Mockery::mock('Symfony\Component\HttpFoundation\RequestStack');
        $request_mock = \Mockery::mock('Symfony\Component\HttpFoundation\Request');
        $cookies_mock = \Mockery::mock('Symfony\Component\HttpFoundation\ParameterBag');

        $request_stack_mock->shouldReceive('getCurrentRequest')->andReturn($request_mock);
        $request_mock->cookies = $cookies_mock;
        $cookies_mock->shouldReceive('get')->with(VisitorIdentificationProvider::COOKIE_NAME)->andReturn(null);


        $provider = new VisitorIdentificationProvider($request_stack_mock, new NullLogger());

        $this->assertTrue(strlen($provider->getVisitorIdentifier())> 5, 'no cookie but has an identifier');
    }

    public function testGetIdentifierWithSetCookie()
    {
        $request_stack_mock = \Mockery::mock('Symfony\Component\HttpFoundation\RequestStack');
        $request_mock = \Mockery::mock('Symfony\Component\HttpFoundation\Request');
        $cookies_mock = \Mockery::mock('Symfony\Component\HttpFoundation\ParameterBag');

        $request_stack_mock->shouldReceive('getCurrentRequest')->andReturn($request_mock);
        $request_mock->cookies = $cookies_mock;
        $cookies_mock->shouldReceive('get')->with(VisitorIdentificationProvider::COOKIE_NAME)->andReturn('CNCPCT_CODE');


        $provider = new VisitorIdentificationProvider($request_stack_mock, new NullLogger());

        $this->assertEquals('CNCPCT_CODE', $provider->getVisitorIdentifier(), 'no cookie but has an identifier');
    }
}
