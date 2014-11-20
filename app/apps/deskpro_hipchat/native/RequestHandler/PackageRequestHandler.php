<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace deskpro_hipchat\RequestHandler;

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
            case 'check-requirements':
                return $this->checkRequirementsAction($context);
            case 'test-settings':
                return $this->testSettingsAction($context);
            default:
                throw $context->createNotFoundException();
        }
    }

    /**
     * @param  ApiPackageRequestContext                   $context
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkRequirementsAction(ApiPackageRequestContext $context)
    {
        return $context->createJsonResponse(array('curl_support' => function_exists('curl_init')));
    }

    /**
     * @param  ApiPackageRequestContext                   $context
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testSettingsAction(ApiPackageRequestContext $context)
    {
        $token = $context->getIn()->getString('api_token');

        $error  = false;
        $client = null;

        $log   = array();
        $log[] = 'token: '.$token;

        $tests   = array();
        $tests[] = function () use (&$log, $token) {
            $log[] = 'Verifying HipChat API is accessible...';

            $api = new \HipChatApi($token);
            try {
                $api->get_rooms();
                $log[] = 'Everything is ok';
            } catch (\Exception $e) {
                $log[] = $e->getMessage();

                return array((string) $e->getCode(), 'API Exception');
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
            'error_code' => $error ? $error[0] : false,
        );

        return $context->createJsonResponse($result_data);
    }
}
