<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_hipchat\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleApiPackageRequest(ApiPackageRequestContext $context)
    {
        switch ($context->getAction()) {
            case 'check-requirements':
                return $this->checkRequirementsAction($context);
            case 'test-settings':
                return $this->testSettingsAction($context);
            default:
                throw $context->createNotFoundException();
        }
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkRequirementsAction(ApiPackageRequestContext $context)
    {
        return $context->createJsonResponse(['curl_support' => function_exists('curl_init')]);
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testSettingsAction(ApiPackageRequestContext $context)
    {
        $token  = $context->getIn()->getString('api_token');
        $target = $context->getIn()->getString('api_target');

        $error  = false;
        $client = null;

        $log   = [];
        $log[] = 'token: '.$token;

        $tests   = [];
        $tests[] = function () use (&$log, $token, $target) {
            $log[] = 'Verifying HipChat API is accessible...';

            $api = new \HipChatApi($token, $target ?: \HipChatApi::DEFAULT_TARGET);
            try {
                $api->get_rooms();
                $log[] = 'Everything is ok';
            } catch (\Exception $e) {
                $log[] = $e->getMessage();

                return [(string) $e->getCode(), 'API Exception'];
            }
        };

        foreach ($tests as $t) {
            $error = $t();
            if ($error) {
                break;
            }
        }

        $result_data = [
            'log'        => implode("\n", $log),
            'error'      => $error ? $error[1] : false,
            'error_code' => $error ? $error[0] : false,
        ];

        return $context->createJsonResponse($result_data);
    }
}
