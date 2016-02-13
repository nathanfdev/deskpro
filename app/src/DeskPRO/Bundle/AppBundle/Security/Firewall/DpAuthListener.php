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
 */
namespace DeskPRO\Bundle\AppBundle\Security\Firewall;

use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\Exception\UsersourceNoEmailException;
use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use DeskPRO\Bundle\AppBundle\Security\DpFormLoginToken;
use DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener;
use Orb\Auth\Adapter\SsoLoginActionInterface;
use Orb\Auth\Result;
use Orb\Log\Loggable;
use Orb\Log\Writer\ArrayWriter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
    const USERSOURCE_TEST = 'usersource_test';

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function requiresAuthentication(Request $request)
    {
        $security_routes = array(
            'portal_login_submit',
            'portal_login_authenticate',
            'portal_login_callback',
            'portal_login_usersource_sso',
            'portal_agent_login',
        );

        return in_array($request->attributes->get('_route'), $security_routes);
    }

    /**
     * Performs authentication.
     *
     * @param Request $request A Request instance
     *
     * @throws AuthenticationException if the authentication fails
     *
     * @return TokenInterface|Response|null The authenticated token, null if full authentication is not possible, or a Response
     */
    protected function attemptAuthentication(Request $request)
    {
        $tokenOrResponse = null;

        if ('portal_login_submit' == $request->attributes->get('_route')) {
            if ($response = $this->checkCaptcha($request)) {
                $this->logLoginFailure($request->get('username'), $request->getClientIp());

                return $response;
            }

            $tokenOrResponse = new DpFormLoginToken($request->get('username'), $request->get('password'));
        } elseif ('portal_agent_login' == $request->attributes->get('_route')) {
            $tokenOrResponse = new AgentImpersonateToken($request->attributes->get('code'));
            $request->getSession()->set('is_impersonating', true);
        } elseif ('portal_login_authenticate' == $request->attributes->get('_route')) {
            $tokenOrResponse = $this->getAuthRedirect($request);
        } elseif ('portal_login_callback' == $request->attributes->get('_route')) {
            $tokenOrResponse = $this->processCallback($request);
        } elseif ('portal_login_usersource_sso' == $request->attributes->get('_route')) {
            $tokenOrResponse = $this->processBackgroundSso($request);
        }

        if ($tokenOrResponse instanceof Response) {
            return $tokenOrResponse;
        }

        return $this->authenticationManager->authenticate($tokenOrResponse);
    }

    protected function checkCaptcha(Request $request)
    {
        $anti_abuse  = $this->container->get('anti_abuse');
        $abuse_check = new LoginAbuseCheck($request->get('username'), $request->getClientIp());
        $abuse_check->markAsCheckOnly();
        $anti_abuse->check($abuse_check);
        if ($abuse_check->isCaptchaRecommended()) {
            $captcha_check = $this->container->get('form.factory')->createNamed(
                '',
                'deskpro_captcha',
                null,
                [
                    'csrf_double_submit_protection' => false,
                ]
            );
            $captcha_check->submit($request->get('deskpro_captcha'));
            if (!$captcha_check->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(
                        [
                            'success' => false,
                            'captcha' => true,
                        ]
                    );
                }

                return new RedirectResponse(
                    $this->container->get('router')->generate(
                        'portal_login',
                        [
                            'retry' => 'captcha',
                        ]
                    )
                );
            }
        }

        return;
    }

    protected function getAuthRedirect(Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session        = $request->getSession();
        $return         = $request->get('return');
        $usersource_id  = $request->get('usersource_id');
        $requestContext = $request->get('context');

        $em = $this->container->get('doctrine.orm.default_entity_manager');
        /** @var \Application\DeskPRO\Auth\AuthenticationManager $auth_manager */
        $auth_manager         = $this->container->get('dp_authentication_manager.user');
        $auth_adapter_factory = $auth_manager->getAuthAdapterFactory();

        if ($usersource_test = $request->get(self::USERSOURCE_TEST)) {
            $session->getFlashBag()->set(self::USERSOURCE_TEST, 1);
        }

        $usersource = $em->find('DeskPRO:Usersource', $usersource_id);
        if (!$usersource) {
            throw new NotFoundHttpException();
        }
        $adapter = $auth_adapter_factory->getAuthAdapter($usersource, $requestContext);

        if (!$usersource_test && !$auth_manager->isUsableUsersource($usersource)) {
            throw new NotFoundHttpException('it is illegal to use this usersource in this context');
        }

        if ($adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
            $result = $adapter->authenticate();

            // The user is already logged in
            if ($result->isValid()) {
                return $this->createTokenFromUsersourceResult($usersource, $result);

                // We expect a redirect to be required
            } elseif ($result->isRedirectRequired()) {
                $return = $request->get('return');
                $session->set('auth_return', $return);

                return $this->redirect($result->getRedirectUrl());
            }

            throw new BadCredentialsException();
        } else {
            $result = $adapter->authenticate();

            if ($result->isValid()) {
                return $this->createTokenFromUsersourceResult($usersource, $result);
            }

            throw new BadCredentialsException();
        }
    }

    protected function processCallback(Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session        = $request->getSession();
        $return         = $request->get('return');
        $usersource_id  = $request->get('usersource_id');
        $requestContext = $request->get('context');
        $em             = $this->container->get('doctrine.orm.default_entity_manager');
        /** @var \Application\DeskPRO\Auth\AuthenticationManager $auth_manager */
        $auth_manager         = $this->container->get('dp_authentication_manager.user');
        $auth_adapter_factory = $auth_manager->getAuthAdapterFactory();

        $usersource = $em->find('DeskPRO:Usersource', $usersource_id);
        if (!$usersource) {
            throw new NotFoundHttpException();
        }

        $usersource_test = $session->getFlashBag()->get(self::USERSOURCE_TEST, array());
        if (!$usersource_test) {
            $usersource_test = $request->get(self::USERSOURCE_TEST);
        }

        if (!$usersource_test && !$auth_manager->isUsableUsersource($usersource)) {
            throw new NotFoundHttpException('it is illegal to use this usersource in this context');
        }

        $adapter = $auth_adapter_factory->getAuthAdapter($usersource, $requestContext);

        $arr_writer = new ArrayWriter();
        if ($usersource_test && $adapter instanceof Loggable && $adapter->getLogger()) {
            $adapter->getLogger()->addWriter($arr_writer);
        }

        // It must be a callback type to be here, so if not redirect back to login
        if (!($adapter instanceof \Orb\Auth\Adapter\CallbackInterface)) {
            $session->getFlashBag()->set('login_failed', true);

            throw new BadCredentialsException();
        }

        $adapter->setCallbackContext($_REQUEST);

        $result = $adapter->authenticate();

        // Valid
        if ($result->isValid()) {
            try {
                $login_processor = new LoginProcessor($usersource, $result->getIdentity());
                $person          = $login_processor->getPerson();
                $person->setLastLoginAt();
                $em->persist($person);
                $em->flush();
            } catch (UsersourceNoEmailException $e) {
                return $this->saveTmpDataAndRedirectToSetEmailPage($usersource, $result);
            }

            if ($usersource_test) {
                // test result
                return $this->container->get('templating')->renderResponse(
                    'DeskPRO:Auth:_sso_test_verified.html.twig', array(
                        'person' => $person,
                        'log'    => $arr_writer->getMessagesAsString(),
                    )
                );
            }

            return $this->createTokenFromPerson($person);
        } elseif ($usersource_test) {
            return $this->container->get('templating')->renderResponse(
                'DeskPRO:Auth:_sso_test_failed.html.twig', array(
                    'log' => implode("\n", $arr_writer->getMessages()),
                )
            );
        }

        throw new BadCredentialsException();
    }

    protected function processBackgroundSso(Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session        = $request->getSession();
        $return         = $request->get('return');
        $usersource_id  = $request->get('usersource_id');
        $requestContext = $request->get('context');
        $em             = $this->container->get('doctrine.orm.default_entity_manager');
        /** @var \Application\DeskPRO\Auth\AuthenticationManager $auth_manager */
        $auth_manager         = $this->container->get('dp_authentication_manager.user');
        $auth_adapter_factory = $auth_manager->getAuthAdapterFactory();

        $usersource = $em->find('DeskPRO:Usersource', $usersource_id);
        if (!$usersource) {
            throw new NotFoundHttpException();
        }

        $adapter = $auth_adapter_factory->getAuthAdapter($usersource, $requestContext);

        if (!$adapter instanceof SsoLoginActionInterface) {
            throw new NotFoundHttpException();
        }

        $arr_writer = new ArrayWriter();
        if (!$usersource_test = $session->getFlashBag()->get(self::USERSOURCE_TEST, array())) {
            $usersource_test = $request->get(self::USERSOURCE_TEST);
        }
        if (!$usersource_test && !$auth_manager->isUsableUsersource($usersource)) {
            throw new NotFoundHttpException('it is illegal to use this usersource in this context');
        }
        if ($usersource_test && $adapter instanceof Loggable && $adapter->getLogger()) {
            $adapter->getLogger()->addWriter($arr_writer);
        }

        $result = $adapter->getSsoLoginActionResult();

        if ($result->isValid()) {
            $token = $this->createTokenFromUsersourceResult($usersource, $result);
            $token->setAttribute(SsoLoginActionInterface::TOKEN_ATTRIBUTE_BACKGROUND_REFRESH, true);

            if (!$token->isAuthenticated()) {
                throw new BadCredentialsException();
            }

            if ($usersource_test) {
                // test result
                return $this->container->get('templating')->renderResponse('DeskPRO:Auth:_sso_test_verified.html.twig', array(
                        'person' => $token->getUser(),
                        'log'    => $arr_writer->getMessagesAsString(),
                    )
                );
            }

            return $token;
        } elseif ($usersource_test) {
            return $this->container->get('templating')->renderResponse('DeskPRO:Auth:_sso_test_failed.html.twig', array(
                    'log' => implode("\n", $arr_writer->getMessages()),
                )
            );
        }

        throw new BadCredentialsException();
    }

    protected function redirect($url)
    {
        return new RedirectResponse($url, 302, array(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER => '1'));
    }

    protected function redirectRoute($route, $params = array())
    {
        return new RedirectResponse($this->container->get('router')->generate($route, $params));
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $usersource
     * @param $result
     * @param $em
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

        return $this->createTokenFromPerson($person);
    }

    /**
     * @param $person
     *
     * @return DpFormLoginToken
     */
    protected function createTokenFromPerson(Person $person)
    {
        return new DpFormLoginToken($person, $person->getPassword(), array_merge(array('ROLE_USER'), $person->getRoles()));
    }

    private function logLoginFailure($email, $ip)
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $person_repo */
        $person_repo    = $this->container->get('doctrine.orm.default_entity_manager')->getRepository('DeskPRO:Person');
        $attempt_person = $person_repo->findOneByEmail($email);
        if ($attempt_person) {
            $this->container->get('doctrine.dbal.default_connection')->insert(
                'login_log',
                array(
                    'person_id'    => $attempt_person->getId(),
                    'area'         => defined('DP_INTERFACE') ? DP_INTERFACE : 'unknown',
                    'is_success'   => 0,
                    'ip_address'   => $ip,
                    'hostname'     => @gethostbyaddr($ip) ?: '',
                    'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
                    'date_created' => date('Y-m-d H:i:s'),
                )
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
}
