<?php

namespace DpTest\DeskPRO\Bundle\ApiBundle\Proxy;

use DeskPRO\Bundle\ApiBundle\Proxy\ApplicationProxyRequest;
use DeskPRO\Bundle\ApiBundle\Proxy\RequestSigningStrategyOauth1;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Proxy\ProxyRequestFactory;
use DeskPRO\Bundle\ApiBundle\Proxy\ProxySignWithHeader;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth1Connection;
use DpTest\DeskProTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProxyRequestFactoryTest extends DeskProTestCase
{
    /**
     * @param array $vars
     * @return \PHPUnit_Framework_MockObject_MockObject | AppStateRepository
     */
    private function  getMockRepository(array $vars)
    {
        $contents = array_map(
            function ($key, $value) {
                $state = new AppState();
                $state->setName($key);
                $state->setValue($value);
                return $state;
            },
            array_keys($vars), $vars
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject | AppStateRepository $repository */
        $repository = $this->getMockBuilder(AppStateRepository::class)
            ->disableOriginalConstructor()->setMethods(['findReadableByName'])
            ->getMock()
        ;

        $repository->method('findReadableByName')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->equalTo(array_keys($vars))
            )
            ->willReturn($contents)
        ;
        return $repository;
    }

    public function testReplaceRequestVariables()
    {
        $instance = new AppInstance();
        /** @var \PHPUnit_Framework_MockObject_MockObject | Person $person */
        $person = $this->getMockBuilder(Person::class)->disableOriginalConstructor()->getMock();

        $stateVars = [
            'subdomain' => json_encode('www'),
            'token' => json_encode('a token'),
            'notAString' => json_encode([ 'not' => 'a string']),
        ];
        $repository = $this->getMockRepository($stateVars);

        $request = new Request();
        $request->headers->set('x-proxy-replacevars', 'true');
        $request->headers->set('x-proxy-autoheaders', 'false');

        $request->headers->set('X-Proxy-Method', 'GET');
        $request->headers->set('X-Proxy-Url', 'http://{{subdomain}}.deskpro.com');
        $request->headers->set('X-Proxy-Header-Authorization', 'token {{token}}');
        $request->headers->set('X-Proxy-Header-MagnumPI', '{{notAString}}');

        $actualProxyRequest = null;
        $factory = new ProxyRequestFactory($repository);
        $actualProxyRequest = $factory->createFromAppRequest($instance, $request, $person);

        $this->assertEquals('http://www.deskpro.com', $actualProxyRequest->getProxyUrl());
        $this->assertEquals(['token a token'], $actualProxyRequest->getProxyHeader('Authorization'));

        $this->assertEquals(['(undefined)'], $actualProxyRequest->getProxyHeader('MagnumPI'));
    }

    public function testCreateFromAppRequestBuildsSignWithHeader()
    {
        $instance = new AppInstance();
        /** @var \PHPUnit_Framework_MockObject_MockObject | Person $person */
        $person = $this->getMockBuilder(Person::class)->disableOriginalConstructor()->getMock();

        $stateVars = [
            'oauth:jira' => json_encode(['clientId' => '5']),
            'oauth:jira:token' => json_encode(['clientId' => '6', 'token' => 'ab'])
        ];

        $trials = [
            'one-var' => ['oauth:jira'],
            'more-vars' => ['oauth:jira', 'oauth:jira:token']
        ];

        /** @var array $vars */
        foreach ($trials as $vars) {
            $trialStateVars = array_intersect_key($stateVars, array_flip($vars));
            $repository = $this->getMockRepository($trialStateVars);

            $request = new Request();
            $request->headers->set('X-Proxy-Method', 'GET');
            $request->headers->set('X-Proxy-Url', 'http://deskpro.com');
            $request->headers->set(ProxySignWithHeader::NAME, 'oauth1 '. implode(' ', $vars));

            $actualProxyRequest = null;
            $factory = new ProxyRequestFactory($repository);
            $actualProxyRequest = $factory->createFromAppRequest($instance, $request, $person);

            $this->assertInstanceOf(ApplicationProxyRequest::class, $actualProxyRequest);
            $signInStrategy = $actualProxyRequest->getSigningStrategy();
            $this->assertInstanceOf(RequestSigningStrategyOauth1::class, $signInStrategy);
        }
    }
}
