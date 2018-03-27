<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\PortalBundle\Controller;

use DpTest\PortalTestCase;

class PortalControllerTest extends PortalTestCase
{
    public function testHomepageLoads()
    {
        $this->installDataSet('fresh');

        $client = $this->getClient();

        $crawler = $client->request('GET', '/');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
    }
}
