<?php

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
