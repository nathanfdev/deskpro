<?php

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\EmailGateway\Fetcher\Office365;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AppBundle\EventListener\RedirectProtectionListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Office365Controller
 * @package Application\AdminInterfaceBundle\Controller
 */
class Office365Controller extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function requestAccessCodeAction(Request $request)
    {
        $id     = $request->get('client_id') ?: $this->settings->get('core_email.office365_oauth_client_id');
        $secret = $request->get('client_secret') ?: $this->settings->get('core_email.office365_oauth_client_secret');

        $oauthClient = Office365::createOauthClient($id, $secret);
        $location    = $oauthClient->getAuthorizationUrl();

        $request->getSession()->set('azureOauthState', $oauthClient->getState());

        $this->em->getRepository(Setting::class)->updateSetting('core_email.office365_oauth_client_id', $id);
        $this->em->getRepository(Setting::class)->updateSetting('core_email.office365_oauth_client_secret', $secret);

        $redirectResponse = new RedirectResponse($location);
        $redirectResponse->headers->set(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, 'true');

        return $redirectResponse;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestAccessTokenAction(Request $request)
    {
        $expectedState = $request->getSession()->get('azureOauthState');
        $request->getSession()->remove('oauthState');
        $providedState = $request->query->get('state');

        if (!isset($expectedState) || !isset($providedState) || $expectedState !== $providedState) {
            return $this->render('AdminInterfaceBundle:TicketAccounts:oauth0-redirect.html.twig', [
                'error'        => 'Invalid auth state',
                'error_detail' => 'The provided auth state did not match the expected value',
            ]);
        }

        // Authorization code should be in the "code" query param
        $authCode = $request->query->get('code');
        if (isset($authCode)) {
            try {
                $id     = $this->settings->get('core_email.office365_oauth_client_id');
                $secret = $this->settings->get('core_email.office365_oauth_client_secret');

                $oauthClient = Office365::createOauthClient($id, $secret);
                $accessToken = $oauthClient->getAccessToken('authorization_code', [
                    'code' => $authCode
                ]);

                return $this->render('AdminInterfaceBundle:TicketAccounts:oauth0-redirect.html.twig', [
                    'access_token'  => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                ]);
            }
            catch (\Exception $e) {
                return $this->render('AdminInterfaceBundle:TicketAccounts:oauth0-redirect.html.twig', [
                    'error'        => 'Error requesting access token',
                    'error_detail' => $e->getMessage(),
                ]);
            }
        }

        return $this->render('AdminInterfaceBundle:TicketAccounts:oauth0-redirect.html.twig', [
            'error'        => $request->query->get('error'),
            'error_detail' => $request->query->get('error_description'),
        ]);
    }


}
