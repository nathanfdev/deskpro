<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace deskpro_magento\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
    /**
     * {@inheritDoc}
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
     * @param  ApiPackageRequestContext                   $context
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testSettingsAction(ApiPackageRequestContext $context)
    {
        $url  = $context->getIn()->getString('url');
        $user = $context->getIn()->getString('api_user');
        $key  = $context->getIn()->getString('api_key');

        $error = false;
        $client = null;

        $log = array();
        $log[] = "url: $url";
        $log[] = "user: $user";
        $log[] = "key: $key";

        $tests = array();
        $tests[] = function () use (&$log) {
            $log[] = "Verifying SoapClient is available...";
            if (!class_exists('\SoapClient')) {
                $log[] = "SOAP support is not enabled in PHP";

                return array('missing_soap', "SOAP support is not enabled in PHP");
            }
            $log[] = "SoapClient is ok";

            return null;
        };

        $tests[] = function () use (&$log) {
            $log[] = "Verifying curl is available...";
            if (!function_exists('curl_init')) {
                $log[] = "curl is not enabled in PHP";

                return array('missing_soap', "curl support is not enabled in PHP");
            }
            $log[] = "curl is ok";

            return null;
        };

        $get_client = function ($url) {
            $url .= '/api?wsdl';
            $handle = @curl_init($url);
            @curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

            $response = @curl_exec($handle);
            $httpCode = @curl_getinfo($handle, CURLINFO_HTTP_CODE);
            @curl_close($handle);
            if($httpCode != 200) {
                return null;
            }

            return new \SoapClient($url);
        };

        $tests[] = function () use (&$log, &$client, $url, $get_client) {
            $log[] = "Connecting to SOAP service...";
            try {
                $error = error_reporting();
                error_reporting($error & ~E_WARNING);
                $client = $get_client($url);
                error_reporting($error);

                if ($client) {
                    $log[] = "Successfully connected to SOAP service";
                } else {
                    $log[] = "Failed: Invalid URL or connection was refused";

                    return array('failed_connection', "Invalid URL or the service refused the connection");
                }
            } catch (\SoapFault $e) {
                $log[] = "Failed: Invalid URL or connection was refused";
                $log[] = "(Exception: {$e->getCode()} {$e->getMessage()}";

                return array('failed_connection', "Invalid URL or the service refused the connection");
            }
        };

        $tests[] = function () use (&$log, &$client, $user, $key) {
            $log[] = "Testing API user and key...";
            try {
                $session = $client->login($user, $key);
                $log[] = "API user and key are correct";
            } catch (\SoapFault $e) {
                $log[] = "API user and/or key is incorrect";
                $log[] = "(Exception: {$e->getCode()} {$e->getMessage()}";

                return array('invalid_api_credentials', "API user and/or key is incorrect");
            }
        };

        foreach ($tests as $t) {
            $error = $t();
            if ($error) {
                break;
            }
        }

        $result_data = array(
            'log'        => implode("\n", $log),
            'error'      => $error ? $error[1] : false,
            'error_code' => $error ? $error[0] : false
        );

        return $context->createJsonResponse($result_data);
    }
}
