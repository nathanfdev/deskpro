<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_us_joomla\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use deskpro_us_joomla\Usersource\Auth\Joomla;
use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;

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
        $joomla = new Joomla([
            'joomla_url'    => $context->getIn()->getString('joomla_url'),
            'joomla_secret' => $context->getIn()->getString('joomla_secret'),
        ]);

        $ar_log = new ArrayWriter();
        $logger = new Logger();
        $logger->addWriter($ar_log);

        $joomla->setLogger($logger);

        $result_data = [
            'log'        => '',
            'error'      => false,
            'error_code' => 0,
        ];

        try {
            $joomla->setFormData([
                'username' => $context->getIn()->getString('username'),
                'password' => $context->getIn()->getString('password'),
            ]);

            $result = $joomla->authenticate();

            if (!$result->isValid()) {
                $result_data['error']      = $result->getMessages('error_message') ?: 'Invalid login';
                $result_data['error_code'] = $result->getMessages('error_code') ?: 'general';
            }
        } catch (\Exception $e) {
            $result_data['error']      = $e->getMessage();
            $result_data['error_code'] = $e->getCode();
        }

        $result_data['log'] = $ar_log->getMessagesAsString();

        return $context->createJsonResponse($result_data);
    }
}
