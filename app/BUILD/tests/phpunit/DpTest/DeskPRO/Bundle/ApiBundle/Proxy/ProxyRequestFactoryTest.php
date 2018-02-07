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
    public function testCreateFromAppRequestBuildsSignWithHeaderWithASingleCredential()
    {
        $instance = new AppInstance();
        /** @var \PHPUnit_Framework_MockObject_MockObject | Person $person */
        $person = $this->getMockBuilder(Person::class)->disableOriginalConstructor()->getMock();

        $stateVars = [];
        $firstVariable = new AppState();
        $stateVars[] = $firstVariable;
        $firstVariable->setName('oauth:jira');
        $firstVariable->setValue('first-value');

        $secondVariable = new AppState();
        $stateVars[] = $secondVariable;
        $secondVariable->setName('oauth:jira:token');
        $firstVariable->setValue('second-value');

        $trials = [
            'one-var' => ['oauth:jira'],
            'more-vars' => ['oauth:jira', 'oauth:jira:token']
        ];

        /** @var array $vars */
        foreach ($trials as $vars) {
            $trialStateVars = array_filter(
                $stateVars,
                function (AppState $state) use ($vars) {
                    return in_array($state->getName(), $vars);
                }
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
                    $this->equalTo($vars)
                )
                ->willReturn($trialStateVars)
            ;

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

    public function testCreateFromAppRequestBuildsSignWithHeader()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject | AppStateRepository $repository */
        $repository = $this->getMockBuilder(AppStateRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findReadableByName'])
            ->getMock()
        ;

        $firstVariable = new AppState();
        $firstVariable->setName('oauth:jira');
        $firstVariable->setValue('first-value');

        $secondVariable = new AppState();
        $secondVariable->setName('oauth:jira:token');
        $firstVariable->setValue('second-value');

        $repository->method('findReadableByName')->willReturn([
            $firstVariable, $secondVariable
        ]);

        $instance = new AppInstance();

        /** @var \PHPUnit_Framework_MockObject_MockObject | Person $person */
        $person = $this->getMockBuilder(Person::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $request = new Request();
        $request->headers->set('X-Proxy-Method', 'GET');
        $request->headers->set('X-Proxy-Url', 'http://deskpro.com');
        $request->headers->set(ProxySignWithHeader::NAME, 'oauth1 oauth:jira oauth:jira:token');

        $actualProxyRequest = null;
        $factory = new ProxyRequestFactory($repository);
        $actualProxyRequest = $factory->createFromAppRequest($instance, $request, $person);

        $this->assertInstanceOf(ApplicationProxyRequest::class, $actualProxyRequest);
        $signInStrategy = $actualProxyRequest->getSigningStrategy();
        $this->assertInstanceOf(RequestSigningStrategyOauth1::class, $signInStrategy);
    }
}
