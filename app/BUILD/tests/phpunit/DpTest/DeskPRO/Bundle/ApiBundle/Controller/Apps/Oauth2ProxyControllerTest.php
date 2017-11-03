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

namespace DpTest\DeskPRO\Bundle\ApiBundle\Controller\Apps;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Bundle\ApiBundle\Controller\Apps\Oauth2ProxyController;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\OauthProviderConnectionLoader;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth2Connection;
use DpTest\AbstractKernelAwareTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class Oauth2ProxyControllerTest extends AbstractKernelAwareTestCase
{
    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|DeskproContainer
     */
    protected function getContainer()
    {
        return $this->getApiKernel()->getContainer();
    }

    public function testDecodeReturnsNullWhenSecretIsNull()
    {
        $actualResult = Oauth2ProxyController::decode('not important', $secret = null);
        $this->assertNull($actualResult, 'OauthProxyController::decode should return null when secret is null');
    }

    public function testEncodeReturnsNullWhenSecretIsNull()
    {
        $actualResult = Oauth2ProxyController::encode(['not important'], $secret = null);
        $this->assertNull($actualResult, 'OauthProxyController::encode should return null when secret is null');
    }

    public function testAuthorizeActionAddsEncodedStateQueryParameter()
    {
        $secret = 'a-secret';

        $container = $this->getContainer();
        $container->getSettingsResolver()->getGlobalSettings()->setArray(['core.app_secret' => $secret]);

        $providerName = 'test';
        $connection = new SerializedOauth2Connection();
        $connection->setProviderName($providerName);
        $connection->setUrlAccessToken('http://127.0.0.1/access_token');
        $connection->setUrlAuthorize('http://127.0.0.1/authorize');
        $connection->setUrlRedirect('http://127.0.0.1/redirect');
        $connection->setUrlResourceOwnerDetails('http://127.0.0.1/me');
        $connection->setClientId('1');
        $connection->setClientSecret('secret');

        /** @var OauthProviderConnectionLoader| \PHPUnit_Framework_MockObject_MockObject $connectionLoader */
        $connectionLoader = $this->getMockBuilder(OauthProviderConnectionLoader::class)
            ->disableOriginalConstructor()->setMethods(['loadOauth2Connection'])
            ->getMock()
        ;
        $connectionLoader->method('loadOauth2Connection')->willReturn($connection);

        $request = new Request();
        $request->query->add([
            'applicationId' => 1,
            'callbackMethod' => 'postMessage',
            'callbackUrl' => 'http://127.0.0.1',
            'state' => 'some state'
        ]);

        $controller = new Oauth2ProxyController();
        $controller->setContainer($container);

        $response = $controller->authorizeAction($connectionLoader, $request);
        $this->assertTrue($response instanceof RedirectResponse, 'expecting a redirect response');


        $query = Request::create($response->getTargetUrl())->query;
        $actualToken = $query->get('state', null);
        $this->assertNotNull($actualToken, 'Oauth proxy must add the state parameter');

        $decodedToken = Oauth2ProxyController::decode($actualToken, $secret);
        $this->assertNotNull($decodedToken, 'Oauth proxy should decode the token when same secret is used');
    }

    public function testGrantAccessActionReturnsBadRequestWhenStateDecodingFails()
    {
        $secret = 'a-secret';

        $container = $this->getContainer();
        $container->getSettingsResolver()->getGlobalSettings()->setArray(['core.app_secret' => $secret]);

        /** @var OauthProviderConnectionLoader| \PHPUnit_Framework_MockObject_MockObject $connectionLoader */
        $connectionLoader = $this->getMockBuilder(OauthProviderConnectionLoader::class)
            ->disableOriginalConstructor()->setMethods(['loadReadable'])
            ->getMock()
        ;

        $request = new Request();
        $request->query->add([
            'applicationId' => 1,
            'response_type' => 'code',
            'code' => 'injected code',
            'state' => 'some state'
        ]);

        $controller = new Oauth2ProxyController();
        $controller->setContainer($container);

        $response = $controller->grantAccessAction(new AppInstance(), $connectionLoader, $request);
        $this->assertNotNull($response);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
