<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\HttpKernel\Exception\NoPermissionException;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\OAuthExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GmailController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function tokenAction(Request $request)
    {
        if ($request->query->has('id') && $request->query->has('secret')) {
            return $this->requestAccessCode($request);
        }

        if ($request->query->has('code')) {
            return $this->requestAccessToken($request);
        }

        if ($request->query->has('error')) {
            throw new NoPermissionException($request->get('error'));
        }

        throw new BadRequestHttpException();
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function requestAccessCode(Request $request)
    {
        $session = $request->getSession();
        $client  = new \Google_Client();
        $client->setClientId($request->get('id'));
        $client->setClientSecret($request->get('secret'));

        $session->set('google.oauth.client_id', $request->get('id'));
        $session->set('google.oauth.client_secret', $request->get('secret'));

        $client->setScopes(\Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setAccessType('offline');
        $backUrl = $this->generateUrl(
            'gmail_token',
            ['back_url' => $request->get('back_url')],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $backUrl = str_replace('local.deskpro', 'localhost', $backUrl);
        $client->setRedirectUri($backUrl);
        $location = $client->createAuthUrl();

        return $this->redirect($location);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function requestAccessToken(Request $request)
    {
        $session = $request->getSession();
        $client  = new \Google_Client();
        $client->setClientId($session->get('google.oauth.client_id'));
        $client->setClientSecret($session->get('google.oauth.client_secret'));
        $client->setScopes(\Google_Service_Gmail::MAIL_GOOGLE_COM);
        $backUrl = $this->generateUrl(
            'gmail_token',
            ['back_url' => $request->get('back_url')],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $client->setRedirectUri($backUrl);

        try {
            $result = $client->authenticate($request->get('code'));
            if (!empty($result['error'])) {
                throw new \Exception($result['description']);
            }
            $token                 = $client->getAccessToken();
            $token['clientId']     = $session->get('google.oauth.client_id');
            $token['clientSecret'] = $session->get('google.oauth.client_secret');
            $request->getSession()->getFlashBag()->add('gmail.oauth.token', $token);
            $session->remove('google.oauth.client_id');
            $session->remove('google.oauth.client_secret');
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('gmail.oauth.error', true);
            $logger = $this->container->get('dp_sys.alerts.event_logger');
            $logger->log(new OAuthExceptionEvent($e));
        }

        $backUrl = $request->get('back_url');

        return $this->redirect($backUrl);
    }
}
