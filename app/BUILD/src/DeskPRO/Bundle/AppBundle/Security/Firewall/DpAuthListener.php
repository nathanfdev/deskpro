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

namespace DeskPRO\Bundle\AppBundle\Security\Firewall;

use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\Exception\UsersourceNoEmailException;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use DeskPRO\Bundle\AppBundle\Security\DpFormLoginToken;
use DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener;
use Orb\Auth\Adapter\SsoLoginActionInterface;
use Orb\Auth\Result;
use Orb\Log\Loggable;
use Orb\Log\Writer\ArrayWriter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

/**
 * This is where the bulk of the logic is for the security system of the portal.
 *
 * This listeners detects various methods of logging in (sso, forms, callbacks, saml, etc) and creates
 * the proper token, which are all handled in the security providers.
 *
 * It may also reject a request right away, return a response, do some logging, etc.
 */
class DpAuthListener extends AbstractAuthenticationListener implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const USERSOURCE_TEST = 'usersource_test';

    /**
     * {@inheritdoc}
     */
    protected function requiresAuthentication(Request $request)
    {
        $security_routes = [
            'portal_login_submit',
            'portal_login_authenticate',
            'portal_login_callback',
            'portal_login_usersource_sso',
            'portal_agent_login',
        ];

        return in_array($request->attributes->get('_route'), $security_routes);
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        $tokenOrResponse = null;

        $username = $request->get('username', '');
        $route    = $request->attributes->get('_route');

        if ('portal_login_submit' == $route && is_scalar($username)) {
            $abuseCheck = $this->createAntiAbuseEvent($request);
            $response   = $this->checkCaptcha($request, $abuseCheck);
            if ($response) {
                $this->logLoginFailure($request->get('username'), $request->getClientIp());

                return $response;
            }

            $tokenOrResponse = new DpFormLoginToken($username, $request->get('password', ''));
        } elseif ('portal_agent_login' == $route) {
            $tokenOrResponse = new AgentImpersonateToken($request->attributes->get('code'));
            $request->getSession()->set('is_impersonating', true);
        } elseif ('portal_login_authenticate' == $route) {
            $tokenOrResponse = $this->getAuthRedirect($request);
        } elseif ('portal_login_callback' == $route) {
            $tokenOrResponse = $this->processCallback($request);
        } elseif ('portal_login_usersource_sso' == $route) {
            $tokenOrResponse = $this->processBackgroundSso($request);
        }

        if ($tokenOrResponse instanceof Response) {
            return $tokenOrResponse;
        } elseif (!$tokenOrResponse) {
            return new RedirectResponse('/login');
        }

        try {
            return $this->authenticationManager->authenticate($tokenOrResponse);
        } catch (AuthenticationException $e) {
            if (isset($abuseCheck)) {
                $this->container->get('anti_abuse')->saveRateLimit($abuseCheck);
            }
            throw $e;
        }
    }

    /**
     * @param Request         $request
     * @param LoginAbuseCheck $abuseCheck
     *
     * @return JsonResponse|RedirectResponse|void
     */
    protected function checkCaptcha(Request $request, LoginAbuseCheck $abuseCheck)
    {
        $antiAbuse = $this->container->get('anti_abuse');
        $antiAbuse->check($abuseCheck);
        if ($abuseCheck->isCaptchaRecommended()) {
            $captchaForm = $this->container->get('form.factory')->create(DpCaptchaType::class, null, [
                'csrf_double_submit_protection' => false,
            ]);
            $captchaForm->submit($request->get('deskpro_captcha'));
            if (!$captchaForm->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'captcha' => true,
                    ]);
                }

                return new RedirectResponse($this->container->get('router')->generate('portal_login', [
                    'retry' => 'captcha',
                ]));
            }
        }
        $abuseCheck->markAsCheckOnly(false);
        $antiAbuse->check($abuseCheck);

        return;
    }

    /**
     * @param Request $request
     *
     * @return LoginAbuseCheck
     */
    protected function createAntiAbuseEvent(Request $request)
    {
        $request->getSession()->set('last_username', $request->get('username'));

        $abuseCheck = new LoginAbuseCheck($request->get('username'), $request->getClientIp());
        $abuseCheck->markAsCheckOnly(true);
        $abuseCheck->setResponse(new RedirectResponse($this->container->get('router')->generate('portal_login')));

        return $abuseCheck;
    }

    /**
     * @param Request $request
     *
     * @return DpFormLoginToken|RedirectResponse
     */
    protected function getAuthRedirect(Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session     = $request->getSession();
        $authManager = $this->container->get('dp_authentication_manager.user');

        $usersource     = $this->getUsersource($request);
        $usersourceTest = $this->isUsersourceTest($request);
        if ($usersourceTest) {
            $session->getFlashBag()->set(self::USERSOURCE_TEST, 1);
        }

        if (!$usersourceTest && !$authManager->isUsableUsersource($usersource)) {
            throw new NotFoundHttpException('it is illegal to use this usersource in this context');
        }

        $adapter = $authManager->getAuthAdapterFactory()->getAuthAdapter($usersource, $request->get('context'));
        if ($adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
            // set return path only if it's defined in query params, otherwise it could be set in auth listener
            // so don't clear it
            $return = $request->get('return');
            if ($return) {
                $session->set('_security.'.$this->providerKey.'.target_path', $return);
            }

            try {
                $result = $adapter->authenticate();
            } catch (\Exception $e) {
                throw new BadCredentialsException('portal.account.login-not-configured');
            }

            // The user is already logged in
            if ($result->isValid()) {
                return $this->createTokenFromUsersourceResult($usersource, $result);

                // We expect a redirect to be required
            } elseif ($result->isRedirectRequired()) {
                $r = $this->redirect($result->getRedirectUrl());
                $r->headers->set(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, 'Yes');

                return $r;
            }

            throw new BadCredentialsException('portal.account.login-invalid');
        } else {
            try {
                $result = $adapter->authenticate();
            } catch (\Exception $e) {
                throw new BadCredentialsException('portal.account.login-not-configured');
            }

            if ($result->isValid()) {
                return $this->createTokenFromUsersourceResult($usersource, $result);
            }

            throw new BadCredentialsException('portal.account.login-invalid');
        }
    }

    /**
     * @param Request $request
     *
     * @return DpFormLoginToken|RedirectResponse|Response
     */
    protected function processCallback(Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session     = $request->getSession();
        $authManager = $this->container->get('dp_authentication_manager.user');

        $usersource     = $this->getUsersource($request);
        $usersourceTest = $this->isUsersourceTest($request);

        if (!$usersourceTest && !$authManager->isUsableUsersource($usersource)) {
            throw new NotFoundHttpException('it is illegal to use this usersource in this context');
        }

        $adapter = $authManager->getAuthAdapterFactory()->getAuthAdapter($usersource, $request->get('context'));
        $writer  = new ArrayWriter();
        if ($usersourceTest && $adapter instanceof Loggable && $adapter->getLogger()) {
            $adapter->getLogger()->addWriter($writer);
        }

        // It must be a callback type to be here, so if not redirect back to login
        if (!$adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
            $session->getFlashBag()->set('login_failed', true);

            throw new BadCredentialsException('portal.account.login-invalid');
        }

        $adapter->setCallbackContext($_REQUEST);
        $result = $adapter->authenticate();

        // Valid
        if ($result->isValid()) {
            $token = $this->createTokenFromUsersourceResult($usersource, $result);
            if ($usersourceTest) {
                return $this->getSuccessTestResponse($token->getUser(), $writer);
            }

            // allow to JWT or SAML to control redirect to specific page after login
            if ($request->get('return')) {
                $session->set('_security.'.$this->providerKey.'.target_path', $request->get('return'));
            }

            return $token;
        }

        if ($usersourceTest) {
            return $this->getFailedTestResponse($writer);
        }

        throw new BadCredentialsException('portal.account.login-invalid');
    }

    /**
     * @param Request $request
     *
     * @return DpFormLoginToken|Response
     */
    protected function processBackgroundSso(Request $request)
    {
        $authManager    = $this->container->get('dp_authentication_manager.user');
        $usersource     = $this->getUsersource($request);
        $usersourceTest = $this->isUsersourceTest($request);

        $adapter = $authManager->getAuthAdapterFactory()->getAuthAdapter($usersource, $request->get('context'));
        if (!$adapter instanceof SsoLoginActionInterface) {
            throw new NotFoundHttpException();
        }
        if (!$usersourceTest && !$authManager->isUsableUsersource($usersource)) {
            throw new NotFoundHttpException('it is illegal to use this usersource in this context');
        }

        $writer = new ArrayWriter();
        if ($usersourceTest && $adapter instanceof Loggable && $adapter->getLogger()) {
            $adapter->getLogger()->addWriter($writer);
        }

        $result = $adapter->getSsoLoginActionResult();
        if ($result->isValid()) {
            $token = $this->createTokenFromUsersourceResult($usersource, $result);
            $token->setAttribute(SsoLoginActionInterface::TOKEN_ATTRIBUTE_BACKGROUND_REFRESH, true);

            if ($usersourceTest) {
                return $this->getSuccessTestResponse($token->getUser(), $writer);
            }

            return $token;
        }

        if ($usersourceTest) {
            return $this->getFailedTestResponse($writer);
        }

        throw new BadCredentialsException('portal.account.login-invalid');
    }

    /**
     * @param string $url
     *
     * @return RedirectResponse
     */
    protected function redirect($url)
    {
        return new RedirectResponse($url, 302, [RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER => '1']);
    }

    /**
     * @param string $route
     * @param array  $params
     *
     * @return RedirectResponse
     */
    protected function redirectRoute($route, $params = [])
    {
        return new RedirectResponse($this->container->get('router')->generate($route, $params));
    }

    /**
     * @param Usersource $usersource
     * @param Result     $result
     *
     * @return DpFormLoginToken
     */
    protected function createTokenFromUsersourceResult(Usersource $usersource, Result $result)
    {
        try {
            $em              = $this->container->get('doctrine.orm.default_entity_manager');
            $login_processor = new LoginProcessor($usersource, $result->getIdentity());
            $person          = $login_processor->getPerson();
            $person->setLastLoginAt();

            $em->persist($person);
            $em->flush();
        } catch (UsersourceNoEmailException $e) {
            return $this->saveTmpDataAndRedirectToSetEmailPage($usersource, $result);
        }

        $token = $this->createTokenFromPerson($person);
        if (!$token->isAuthenticated()) {
            throw new BadCredentialsException('portal.account.login-invalid');
        }

        return $token;
    }

    /**
     * @param $person
     *
     * @return DpFormLoginToken
     */
    protected function createTokenFromPerson(Person $person)
    {
        return new DpFormLoginToken($person, $person->getPassword(), array_merge(['ROLE_USER'], $person->getRoles()));
    }

    /**
     * @param string $email
     * @param string $ip
     */
    private function logLoginFailure($email, $ip)
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $person_repo */
        $person_repo    = $this->container->get('doctrine.orm.default_entity_manager')->getRepository('DeskPRO:Person');
        $attempt_person = $person_repo->findOneByEmail($email);
        if ($attempt_person) {
            $this->container->get('doctrine.dbal.default_connection')->insert(
                'login_log',
                [
                    'person_id'    => $attempt_person->getId(),
                    'area'         => defined('DP_INTERFACE') ? DP_INTERFACE : 'unknown',
                    'is_success'   => 0,
                    'ip_address'   => $ip,
                    'hostname'     => @gethostbyaddr($ip) ?: '',
                    'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
                    'date_created' => date('Y-m-d H:i:s'),
                ]
            );
        }
    }

    /**
     * @param $usersource
     * @param $result
     *
     * @return RedirectResponse
     */
    protected function saveTmpDataAndRedirectToSetEmailPage(Usersource $usersource, Result $result)
    {
        // a usersource did NOT provide an email, and we were about to make a new person without an email.
        // instead, trigger our workflow that requires the user to give us an email and verify it before we proceeed.
        $tmp_auth = $this->container->get('usersource_identity_saver')->save($usersource, $result->getIdentity());

        return $this->redirectRoute('user_validate_usersource_email', ['tmp_auth' => $tmp_auth]);
    }

    /**
     * @param Request $request
     *
     * @return Usersource
     */
    protected function getUsersource(Request $request)
    {
        $em         = $this->container->get('doctrine.orm.default_entity_manager');
        $usersource = $em->find(Usersource::class, $request->get('usersource_id'));
        if (!$usersource) {
            throw new NotFoundHttpException();
        }

        return $usersource;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isUsersourceTest(Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();

        return $session->getFlashBag()->get(self::USERSOURCE_TEST, []) || $request->get(self::USERSOURCE_TEST);
    }

    /**
     * @param Person      $person
     * @param ArrayWriter $writer
     *
     * @return Response
     */
    protected function getSuccessTestResponse(Person $person, ArrayWriter $writer)
    {
        return $this->container->get('templating')->renderResponse('DeskPRO:Auth:_sso_test_verified.html.twig', [
            'person' => $person,
            'log'    => $writer->getMessagesAsString(),
        ]);
    }

    /**
     * @param ArrayWriter $writer
     *
     * @return Response
     */
    protected function getFailedTestResponse(ArrayWriter $writer)
    {
        return $this->container->get('templating')->renderResponse('DeskPRO:Auth:_sso_test_failed.html.twig', [
            'log' => $writer->getMessagesAsString(),
        ]);
    }
}
