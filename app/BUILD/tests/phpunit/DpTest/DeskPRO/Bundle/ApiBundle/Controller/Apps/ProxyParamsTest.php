<?php

namespace DpTest\DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\Controller\Apps\ProxyParams;
use DpTest\DeskProTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProxyParamsTest extends DeskProTestCase
{
    public function testGetQualifiedDpParamNameReturnsSomeSortOfQualifiedName()
    {
        $paramName = 'extra';
        $qualified = ProxyParams::qualifyDpParamName($paramName);

        $this->assertTrue($paramName !== $qualified);
        $this->assertNotEmpty($qualified);
    }


    public function testGetExtraQueryParamsReturnsNonEmptyList()
    {
        $paramName = 'extra';
        $request = Request::create('127.0.0.1');
        $request->query->set($paramName, 'param');
        $request->query->set('jingoist', 'maoist');
        $request->query->set(ProxyParams::qualifyDpParamName($paramName), 'dp-param');

        $expected = [ $paramName => 'param', 'jingoist' => 'maoist' ];
        $actual = ProxyParams::getExtraQueryParams($request);

        $this->assertEquals($expected, $actual);
    }
}
