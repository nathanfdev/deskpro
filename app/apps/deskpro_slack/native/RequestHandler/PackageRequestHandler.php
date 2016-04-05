<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace deskpro_slack\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

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
        return $context->createJsonResponse(array('curl_support' => function_exists('curl_init')));
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

        $log   = array();
        $log[] = 'webhook url: '.$webhook_url;

        try {
            $client = new GuzzleClient();
            $res = $client->request(
                'POST',
                $webhook_url,
                ['form_params' => ['payload' => json_encode(['text' => 'Link successful'])]]
            );
            $log[] = $res->getStatusCode() . ' ' . $res->getBody();
        } catch (\Exception $e) {
            $error = [$e->getCode(), $e->getMessage()];
        }

        $result_data = array(
            'log'        => implode("\n", $log),
            'error'      => $error ? $error[1] : false,
            'error_code' => $error ? $error[0] : false,
        );

        return $context->createJsonResponse($result_data);
    }
}
