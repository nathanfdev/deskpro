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

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\OAuthExceptionEvent;
use Symfony\Component\HttpFoundation\Request;

class GmailController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function requestAccessCodeAction(Request $request)
    {
        if (!$id = $this->settings->get('core_email.google_oauth_client_id')) {
            throw new \RuntimeException('Google OAuth client ID not found');
        }
        if (!$secret = $this->settings->get('core_email.google_oauth_secret')) {
            throw new \RuntimeException('Google OAuth client secret not found');
        }

        $client = new \Google_Client();
        $client->setClientId($id);
        $client->setClientSecret($secret);

        $client->setScopes([\Google_Service_Gmail::MAIL_GOOGLE_COM]);
        $client->setAccessType('offline');
        $backUrl = 'urn:ietf:wg:oauth:2.0:oob';

        $client->setRedirectUri($backUrl);
        $location = $client->createAuthUrl();

        return $this->redirect($location);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestAccessTokenAction(Request $request)
    {
        if (!$id = $this->settings->get('core_email.google_oauth_client_id')) {
            throw new \RuntimeException('Google OAuth client ID not found');
        }
        if (!$secret = $this->settings->get('core_email.google_oauth_secret')) {
            throw new \RuntimeException('Google OAuth client secret not found');
        }
        $code = $request->get('code');

        $client = new \Google_Client();
        $client->setClientId($id);
        $client->setClientSecret($secret);
        $client->setScopes([\Google_Service_Gmail::MAIL_GOOGLE_COM]);
        $backUrl = 'urn:ietf:wg:oauth:2.0:oob';
        $client->setRedirectUri($backUrl);

        try {
            $result = $client->authenticate($code);
            if (!empty($result['error'])) {
                throw new \Exception($result['description']);
            }
            $token = $client->getAccessToken();

            return $this->createJsonResponse($token);
        } catch (\Exception $e) {
            $logger = $this->container->get('dp_sys.alerts.event_logger');
            $logger->log(new OAuthExceptionEvent($e));

            return $this->createJsonResponse(['error' => $e->getMessage()]);
        }
    }
}
