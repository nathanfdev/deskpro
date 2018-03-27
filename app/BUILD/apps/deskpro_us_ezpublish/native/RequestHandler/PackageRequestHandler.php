<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_ezpublish\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use Application\DeskPRO\Usersource\UsersourceTester;
use deskpro_us_ezpublish\Usersource\AppOptionsMapper;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleApiPackageRequest(ApiPackageRequestContext $context)
    {
        switch ($context->getAction()) {
            case 'test-settings':
                return $this->testSettingsAction($context);
                break;
            default:
                throw $context->createNotFoundException();
        }
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testSettingsAction(ApiPackageRequestContext $context)
    {
        $username = $context->getIn()->getString('username');
        $password = $context->getIn()->getString('password');
        $options  = AppOptionsMapper::getOptions($context->getIn()->getCleanValueArray('settings'));

        $tester = UsersourceTester::createFromOptions('Application\\DeskPRO\\Usersource\\Adapter\\EzPublish', $options);
        $tester->test($username, $password);

        $result_data = [
            'log'      => $tester->getLog(),
            'raw_data' => $tester->getRawData(),
            'is_valid' => $tester->isValid(),
        ];

        return $context->createJsonResponse($result_data);
    }
}
