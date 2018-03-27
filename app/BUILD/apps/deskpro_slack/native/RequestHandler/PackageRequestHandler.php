<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_slack\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;

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
        $webhook_url = $context->getIn()->getString('webhook_url');

        $error  = false;
        $client = null;

        $log   = [];
        $log[] = 'webhook url: '.$webhook_url;

        try {
            $client = new HttpClient();
            $res    = $client->request(
                'POST',
                $webhook_url,
                ['form_params' => ['payload' => json_encode(['text' => 'Link successful'])]]
            );
            $log[] = $res->getStatusCode().' '.$res->getBody();
        } catch (\Exception $e) {
            $error = [$e->getCode(), $e->getMessage()];
        }

        $result_data = [
            'log'        => implode("\n", $log),
            'error'      => $error ? $error[1] : false,
            'error_code' => $error ? $error[0] : false,
        ];

        return $context->createJsonResponse($result_data);
    }
}
