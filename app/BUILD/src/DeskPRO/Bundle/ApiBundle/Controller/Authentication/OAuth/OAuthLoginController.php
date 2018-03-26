<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication\OAuth;

use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\Entity\OAuthClient;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Form\Type\OAuth\OAuthLoginType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class OAuthLoginController.
 *
 * @ApiModes("all")
 * @Rest\Route("/oauth/{context}/{authClient}", requirements={"context"="(agent|user)"})
 * @ApiUserContext("open")
 */
class OAuthLoginController extends BaseController
{
    /**
     * @ParamConverter(name="authClient", converter="oauth_client", options={"html": true})
     *
     * @Rest\Get("", name="api_oauth_login")
     * @Rest\Post("")
     *
     * @param string      $context
     * @param Request     $request
     * @param OAuthClient $authClient
     *
     * @return Response
     */
    public function loginAction($context, Request $request, OAuthClient $authClient = null)
    {
        if ($context === 'agent') {
            $authManager = $this->get('dp_authentication_manager.agent');
        } else {
            $authManager = $this->get('dp_authentication_manager.user');
        }

        if (!$authClient || $authClient->getContext() !== $context) {
            return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:login_error.html.twig', [
                'message' => 'api.oauth.error_not_found',
            ]);
        }

        if (!$authManager->hasFormLoginCapability()) {
            return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:login_error.html.twig', [
                'message' => 'portal.account.login-form-disabled',
            ]);
        }

        $redirectUrl = $request->query->get('redirect_uri');
        if (!$redirectUrl && count($authClient->getRedirectUris()) === 1) {
            $redirectUrl = $authClient->getRedirectUris()[0];
        }
        if (!$redirectUrl) {
            return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:login_error.html.twig', [
                'message' => 'api.oauth.error_not_redirect_uri',
            ]);
        }

        // authorized, redirect to /auth page
        $person = $this->getUser();
        if ($person instanceof Person) {
            if ($context === OAuthClient::CONTEXT_AGENT && !$person->isActiveAgent()) {
                return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:login_error.html.twig', [
                    'message' => 'api.oauth.error_access_denied',
                ]);
            }

            return $this->redirectToRoute('fos_oauth_server_authorize', [
                'client_id'     => $authClient->getPublicId(),
                'redirect_uri'  => $redirectUrl,
                'response_type' => $authClient->getResponseType(),
                'scope'         => ApiToken::SCOPE_SESSION,
            ]);
        }

        // handle login form
        $sessionData = $this->get('api_portal_session_reader')->getFromRequest($request);

        $captchaForm  = null;
        $lastUsername = isset($sessionData['last_username']) ? $sessionData['last_username'] : null;
        $abuseCheck   = new LoginAbuseCheck($lastUsername, $request->getClientIp());
        $abuseCheck->markAsCheckOnly();
        $this->get('anti_abuse')->check($abuseCheck);
        if ($abuseCheck->isCaptchaRecommended()) {
            $captchaForm = $this->createForm(DpCaptchaType::class, null, [
                'csrf_protection' => false,
            ]);
        }

        if (isset($sessionData['_security.last_error'])) {
            $loginError = $sessionData['_security.last_error'];
            $loginError = $loginError instanceof AuthenticationException
                ? $loginError->getMessage()
                : 'portal.account.login-invalid';
        } else {
            $loginError = null;
        }

        $form = $this->createForm(OAuthLoginType::class, [
            'username' => $lastUsername,
        ]);

        return $this->container->get('templating')->renderResponse('ApiBundle:OAuth:login.html.twig', [
            'auth_manager'         => $authManager,
            'auth_client'          => $authClient,
            'form'                 => $form->createView(),
            'login_error'          => $loginError,
            'login_captcha_failed' => $request->get('retry') == 'captcha',
            'login_route'          => $request->attributes->get('_route'),
            'login_context'        => $context,
            'lockout_error'        => $abuseCheck->isLockoutRecommended(),
            'lockout_time'         => $abuseCheck->getLockoutTime(true),
            'captcha_form'         => $captchaForm ? $captchaForm->createView() : null,
            'redirect_uri'         => $redirectUrl,
        ]);
    }
}
