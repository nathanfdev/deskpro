<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_magento\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use Composer\CaBundle\CaBundle;

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
        $url  = $context->getIn()->getString('url');
        $user = $context->getIn()->getString('api_user');
        $key  = $context->getIn()->getString('api_key');

        $error  = false;
        $client = null;

        $log   = [];
        $log[] = "url: $url";
        $log[] = "user: $user";
        $log[] = "key: $key";

        $tests   = [];
        $tests[] = function () use (&$log) {
            $log[] = 'Verifying SoapClient is available...';
            if (!class_exists('\SoapClient')) {
                $log[] = 'SOAP support is not enabled in PHP';

                return ['missing_soap', 'SOAP support is not enabled in PHP'];
            }
            $log[] = 'SoapClient is ok';

            return;
        };

        $tests[] = function () use (&$log) {
            $log[] = 'Verifying curl is available...';
            if (!function_exists('curl_init')) {
                $log[] = 'curl is not enabled in PHP';

                return ['missing_soap', 'curl support is not enabled in PHP'];
            }
            $log[] = 'curl is ok';

            return;
        };

        $get_client = function ($url) use ($context) {
            $url .= '/api?wsdl';
            $handle = @curl_init($url);
            @curl_setopt($handle,  CURLOPT_RETURNTRANSFER, true);
            $cainfo = CaBundle::getBundledCaBundlePath();
            if (file_exists($cainfo)) {
                @curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
                @curl_setopt($handle, CURLOPT_CAINFO, $cainfo);
            }

            $response = @curl_exec($handle);
            $httpCode = @curl_getinfo($handle, CURLINFO_HTTP_CODE);
            @curl_close($handle);
            if ($httpCode != 200) {
                return;
            }

            $v = libxml_disable_entity_loader(false);
            $c = new \Application\DeskPRO\SoapClient\SafeSoapClient($url);
            libxml_disable_entity_loader($v);

            return $c;
        };

        $tests[] = function () use (&$log, &$client, $url, $get_client) {
            $log[] = 'Connecting to SOAP service...';
            try {
                $error = error_reporting();
                error_reporting($error & ~E_WARNING);
                $client = $get_client($url);
                error_reporting($error);

                if ($client) {
                    $log[] = 'Successfully connected to SOAP service';
                } else {
                    $log[] = 'Failed: Invalid URL or connection was refused';

                    return ['failed_connection', 'Invalid URL or the service refused the connection'];
                }
            } catch (\SoapFault $e) {
                $log[] = 'Failed: Invalid URL or connection was refused';
                $log[] = "(Exception: {$e->getCode()} {$e->getMessage()}";

                return ['failed_connection', 'Invalid URL or the service refused the connection'];
            }
        };

        $tests[] = function () use (&$log, &$client, $user, $key) {
            $log[] = 'Testing API user and key...';
            try {
                $session = $client->login($user, $key);
                $log[]   = 'API user and key are correct';
            } catch (\SoapFault $e) {
                $log[] = 'API user and/or key is incorrect';
                $log[] = "(Exception: {$e->getCode()} {$e->getMessage()}";

                return ['invalid_api_credentials', 'API user and/or key is incorrect'];
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
