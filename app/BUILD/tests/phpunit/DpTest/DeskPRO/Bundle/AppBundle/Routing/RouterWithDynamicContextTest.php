<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Routing;

use DpTest\PortalTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RouterWithDynamicContextTest.
 */
class RouterWithDynamicContextTest extends PortalTestCase
{
    public function testTrailingSlashRedirect()
    {
        $request  = Request::create('/api/people/524/');
        $response = $this->getContainer()->get('dp.dynamic_context_router')->matchRequest($request);

        $this->assertEquals('remove_trailing_slash', $response['_route']);
    }
}
