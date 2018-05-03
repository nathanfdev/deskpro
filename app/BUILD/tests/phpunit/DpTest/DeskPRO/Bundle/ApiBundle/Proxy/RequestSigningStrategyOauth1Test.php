<?php

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

        $trials = [
            [
                $serializer->serialize($firstConnection, 'json'),
                $serializer->serialize($secondConnection, 'json')
            ],
            [
                $serializer->serialize($firstConnection, 'json'),
                json_decode($serializer->serialize($secondConnection, 'json'), true)
            ],
            [
                json_decode($serializer->serialize($firstConnection, 'json'), true),
                json_decode($serializer->serialize($secondConnection, 'json'), true)
            ],
            [
                json_decode($serializer->serialize($firstConnection, 'json'), true),
                $serializer->serialize($secondConnection, 'json')
            ],
        ];

        /** @var RequestSigningStrategyOauth1 $strategy */
        $strategy = null;
        foreach ($trials as $trial) {
            $strategy = RequestSigningStrategyOauth1::fromRequestVariables($trial);
        }

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
        $actualException = null;
        try {
            $strategy = RequestSigningStrategyOauth1::fromRequestVariables([
                json_encode('not an array')
            ]);
            $strategy->configureProxyClient(new HttpProxyClientBuilder());
        } catch (RequestSigningStrategyException $e) {
            $actualException = $e;
        }
        $this->assertNotNull($actualException);


        $actualException = null;
        try {
            $strategy = RequestSigningStrategyOauth1::fromRequestVariables([
                json_encode('not an array'),
                json_encode(['value' => 'key'])
            ]);
            $strategy->configureProxyClient(new HttpProxyClientBuilder());
        } catch (RequestSigningStrategyException $e) {
            $actualException = $e;
        }
        $this->assertNotNull($actualException);
    }
}
