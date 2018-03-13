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

namespace DpTest\DeskPRO\Bundle\ApiBundle\Proxy;

use DeskPRO\Bundle\ApiBundle\Proxy\HttpProxyClientBuilder;
use DeskPRO\Bundle\ApiBundle\Proxy\RequestSigningStrategyException;
use DeskPRO\Bundle\ApiBundle\Proxy\RequestSigningStrategyOauth1;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth1Connection;
use DpTest\DeskProTestCase;

class RequestSigningStrategyOauth1Test extends DeskProTestCase
{
    public function testConfigureProxyClientBuildsExpectedConnection()
    {
        $firstConnection = new SerializedOauth1Connection();
        $firstConnection->setProviderName('jira');
        $firstConnection->setClientId('100');
        $firstConnection->setClientSecret('secret');
        $firstConnection->setRSAPrivateKey('private key');
        $firstConnection->setToken('token');
        $firstConnection->setTokenSecret('tokenSecret');
        $firstConnection->setUrlAuthorization('http://authorization');
        $firstConnection->setUrlRedirect('http://redirect');
        $firstConnection->setUrlTemporaryCredentials('http://temporary');
        $firstConnection->setUrlUserDetails('http://user-details');

        $secondConnection = clone $firstConnection;
        $secondConnection->setTokenSecret('expected_tokenSecret');
        $secondConnection->setToken('expected_token');

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $strategy = new RequestSigningStrategyOauth1([
            $serializer->serialize($firstConnection, 'json'),
            $serializer->serialize($secondConnection, 'json')
        ]);

        /** @var \PHPUnit_Framework_MockObject_MockObject|HttpProxyClientBuilder $builder */
        $builder = $this->getMockBuilder(HttpProxyClientBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['useOauth1SigningStrategy'])
            ->getMock()
        ;
        /**
         * @var SerializedOauth1Connection $actualConnection
         */
        $actualConnection = null;
        $builder->method('useOauth1SigningStrategy')->willReturnCallback(function (SerializedOauth1Connection $conn) use (&$actualConnection) {
            $actualConnection = $conn;
        });

        $strategy->configureProxyClient($builder);
        $this->assertNotNull($actualConnection);
        $this->assertEquals('expected_token', $actualConnection->getToken());
        $this->assertEquals('expected_tokenSecret', $actualConnection->getTokenSecret());
    }

    public function testConfigureProxyClientThrowsExceptionWhenCredentialUnserializationFails()
    {
        $strategy = new RequestSigningStrategyOauth1([
            json_encode('not an array')
        ]);
        $actualException = null;
        try {
            $strategy->configureProxyClient(new HttpProxyClientBuilder());
        } catch (RequestSigningStrategyException $e) {
            $actualException = $e;
        }
        $this->assertNotNull($actualException);


        $strategy = new RequestSigningStrategyOauth1([
            json_encode('not an array'),
            json_encode(['value' => 'key'])
        ]);

        $actualException = null;
        try {
            $strategy->configureProxyClient(new HttpProxyClientBuilder());
        } catch (RequestSigningStrategyException $e) {
            $actualException = $e;
        }
        $this->assertNotNull($actualException);
    }
}
