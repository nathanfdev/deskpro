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

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Controller\AbstractController;
use Application\DeskPRO\Entity\LoginLog;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Entity\WhiteListedIp;
use Application\DeskPRO\EntityRepository\LoginLog as LoginLogRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\EntityRepository\PersonUsersourceAssoc as PersonUsersourceAssocRepository;
use Application\DeskPRO\EntityRepository\Session as SessionRepository;
use Application\DeskPRO\EntityRepository\TmpData as TmpDataRepository;
use Application\DeskPRO\HttpFoundation\Cookie;
use Application\DeskPRO\HttpFoundation\LegacyRequestUtils;
use Application\DeskPRO\People\EmailAddressValidator;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Service\CheckWhitelistedIP;
use Application\DeskPRO\Settings\LoginRateLimitSettings;
use Application\DeskPRO\Translate\SystemLanguage;
use Application\DeskPRO\Twig\AppVariable;
use Application\DeskPRO\Usersource\Adapter\ActiveDirectory;
use Application\DeskPRO\Usersource\Adapter\Ldap;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\PasswordResetAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Exception\AntiAbuseException;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener;
use DeskPRO\Bundle\PortalBundle\Twig\Environment;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use DpSys\License;
use Exception;
use Orb\Auth\Adapter\AdapterInterface;
use Orb\Auth\Adapter\CallbackInterface;
use Orb\Auth\Adapter\SamlAdapterInterface;
use Orb\Auth\Adapter\SsoLoginActionInterface;
use Orb\Auth\Result;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;
use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Web;
use Orb\Validator\StringEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginController extends AbstractController
{
    /** @var string */
    protected $tplPrefix = 'UserBundle:Login';
    /** @var string */
    protected $routePrefix = 'user';

    const USERSOURCE_TEST = 'usersource_test';

    /**
     * @var UsersourceManager
     */
    protected $usersource_manager;

    /**
     * @var AuthenticationManager
     */
    protected $auth_manager;

    public function init()
    {
        parent::init();

        $this->auth_manager       = $this->container->getSystemService('authentication_manager');
        $this->usersource_manager = $this->container->getSystemService('usersource_manager');

        $GLOBALS['DP_SET_SKIP_CACHE'] = true;
    }

    /**
     * @return AppVariable
     */
    protected function getTplGlobals()
    {
        /** @var Environment $twig */
        $twig = $this->get('twig');
        foreach ($twig->getGlobals() as $k => $v) {
            if ($v instanceof AppVariable) {
                return $v;
            }
        }

        throw new \RuntimeException('No AppVariable in twig.');
    }

    /**
     * @return bool
     */
    protected function loginViaToken()
    {
        if (($token = $this->in->getString('tok')) && strpos($token, '-')) {
            list($person_id, $login_token) = explode('-', $token, 2);
            /** @var Person $person */
            $person = $this->em()->find('DeskPRO:Person', $person_id);
            if (!$person || !$person->checkPassword($login_token)) {
                $person = null;
            }

            // If this is a brand new install, we could have an automatic login token to check
            if (!$person) {
                $first        = Arrays::getFirstItem($this->container->getAgentData()->getAgents());
                $install_time = $this->settings->get('core.install_timestamp');

                if ($first && $install_time && $install_time > (time() - 3600)) {
                    $secret = sha1($first->secret_string.$first->salt);
                    if (Util::checkStaticSecurityToken($token, $secret)) {
                        $person = $first;
                    }
                }
            }

            if ($person) {
                $set_active = false;
                if (!$person->date_last_login) {
                    $set_active = true;
                }

                App::setCurrentPerson($person);
                $this->rememberMe($person);
                if ($set_active) {
                    $this->setAgentIsAvailable($person);
                    $person->setLastLoginAt();
                }

                $this->em()->persist($person);
                $this->em()->flush();

                $this->session->set('auth_person_id', $person->getId());
                $this->session->set('dp_interface', DP_INTERFACE);
                $this->session->setFlash('is_from_login', 'yes');
                $this->session->save();

                return true;
            }
        }

        return false;
    }

    protected function _logoutPerson()
    {
        // When an agent actually logs out, we should be clearing the state
        $person = $this->session->getPerson();
        if ($person['is_agent']) {
            $this->db->executeUpdate('
                DELETE FROM people_prefs
                WHERE person_id = ? AND name = ?
            ', [$person['id'], 'agent.ui.state']);
        }

        $this->session->invalidate();
        $this->session->save();

        $this->session->setFlash('is_from_logout', 'yes');

        $cookies = ['dpsid-agent', 'dpsid-admin', 'dpreme'];
        foreach ($cookies as $cookie_name) {
            if (!empty($_COOKIE[$cookie_name])) {
                /** @var SessionRepository $sessionRepository */
                $sessionRepository = $this->em()->getRepository(Session::class);
                $sess2             = $sessionRepository->getSessionFromCode($_COOKIE[$cookie_name]);
                if ($sess2) {
                    $this->em()->remove($sess2);
                    $this->em()->flush();
                }
            }
        }
        $this->deleteCookies($cookies);

        if ($sid = Arrays::findPropertyPath($_COOKIE, '[dpsid]')) {
            App::getDb()->executeUpdate(
                '
                    DELETE
                    FROM sess_data
                    WHERE sess_id = ?
                ',
                [
                    $sid,
                ]
            );
            $this->deleteCookies(['dpsid']);
        }
        App::setCurrentPerson(new PersonGuest());

        Cookie::makeCookie('dplogout', 1, 0)->send();
        $this->deleteCookies(['dp-guest-cache']);
    }

    /**
     * @param $usersource_id
     *
     * @return Response
     */
    public function samlSingleLogoutServiceAction($usersource_id)
    {
        $usersource = $this->em()->find(Usersource::class, $usersource_id);
        if (!$usersource) {
            throw $this->createNotFoundException();
        }
        $adapter = $this->_initUserSourceAdapter($usersource, $this->in->getString('context'), $usersource->type);

        if ($adapter instanceof SamlAdapterInterface) {
            $this->_logoutPerson();

            return $adapter->performSingleLogOutService();
        }

        throw $this->createNotFoundException('usersource / adapter not suitable for SLS');
    }

    /**
     * @param $usersource_id
     *
     * @return Response
     */
    public function samlMetadataAction($usersource_id)
    {
        $usersource = $this->em()->find(Usersource::class, $usersource_id);
        if (!$usersource) {
            throw $this->createNotFoundException();
        }
        $adapter = $this->_initUserSourceAdapter($usersource, $this->in->getString('context'), $usersource->type);

        if ($adapter instanceof SamlAdapterInterface) {
            return $adapter->getMetadataXmlResponse();
        }

        throw $this->createNotFoundException('usersource / adapter not suitable for SLS');
    }

    /**
     * @param Request $request
     *
     * @throws ConnectionException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function authenticateLocalAction(Request $request)
    {
        $return = LegacyRequestUtils::readReturnParam($this->request);

        if ($this->request->getMethod() != 'POST') {
            return $this->redirectRoute('user_login');
        }

        if (!$this->in->getBool('agent_login') && !$this->consumeRequest('user_login')) {
            return $this->redirectRoute($this->routePrefix.'_login');
        }

        try {
            $this->ensureRequestToken('user_login');
        } catch (HttpException $e) {
            $this->session->setFlash('request_token_expired', true);

            return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
        }

        // Form wasnt inputted (eg direct url)
        $inputEmail = $this->in->getString('email');
        if (!$inputEmail || !$this->in->getString('password')) {
            if ($request->getMethod() == 'POST') {
                $this->session->set('failed_login_name', $inputEmail);
                $this->session->save();
            }

            return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
        }

        $check = new LoginAbuseCheck($inputEmail, $request->getClientIp());
        try {
            $this->container->get('anti_abuse')->check($check);
        } catch (AntiAbuseException $e) {
            $this->handleLoginAttempt($request);
        }

        if ($check->isLockoutRecommended()) {
            $this->session->set('failed_login_name', $inputEmail);
            $this->session->save();

            return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
        }

        $captcha = null;

        if ($check->isCaptchaRecommended()) {
            $form = $this->createForm(DpCaptchaType::class);

            $form->submit($request->request->get('deskpro_captcha'));
            if (!$form->isValid()) {
                $this->session->set('failed_login_name', $inputEmail);
                $this->session->setFlash('captcha_login_error', true);
                $this->session->save();

                return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
            }
        }

        $result = $this->authLocalInput();

        if (!$result->isValid()) {
            // If this is an agent or admin and its an ldap error, show them an actual error page
            if (isset($GLOBALS['DP_AUTH_EXCEPTION']) && isset($GLOBALS['DP_AUTH_EXCEPTION_ADAPTER'])) {
                $adapter = $GLOBALS['DP_AUTH_EXCEPTION_ADAPTER'];
                if (DP_INTERFACE != 'user' && ($adapter instanceof Ldap || $adapter instanceof ActiveDirectory)) {
                    if (!extension_loaded('ldap')) {
                        return $this->render(
                            'UserBundle:Legacy:error.html.twig',
                            [
                                'error_title'   => 'LDAP Extension Required',
                                'error_message' => 'Your server does not have the LDAP extension enabled so your login could not be processed. See: http://www.php.net/manual/en/ldap.installation.php',
                            ]
                        );
                    }
                }
            }
            $this->container->get('anti_abuse')->saveRateLimit($check);
            $this->handleLoginAttempt($request);
            $this->session->set('failed_login_name', $inputEmail);
            $this->session->set('failed_to_login', true);
            $this->session->save();

            return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
        }

        $identity = $result->getIdentity();
        /** @var Person $person */
        $person = $identity['person'];

        $lang = $this->container->getTranslator()->getLanguage();
        if ($lang->getId() && !($lang instanceof SystemLanguage)) {
            $person->language = $lang;
        }

        /** @var EmailAddressValidator $emailValidator */
        $emailValidator = $this->container->getSystemService('email_address_validator');
        if ($person->isDisabled() || $emailValidator->personHasBannedEmail($person)) {
            $this->session->setFlash('email_banned', true);
            $this->session->save();

            return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
        }

        if (!isset($GLOBALS['DP_LOGIN_VIA_TOKEN'])) {
            $person->setLastLoginAt();
        }

        $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if ($browser) {
            $person->browser = $browser;
        }

        $this->em()->persist($person);
        $this->em()->flush();

        $this->session->invalidate();
        $this->session->set('auth_person_id', $identity->getIdentity());
        $this->session->set('dp_interface', DP_INTERFACE);
        $this->session->setFlash('is_from_login', 'yes');
        $this->session->set('auth_by', $this->auth_manager->getAuthBy());
        $this->session->save();

        App::setCurrentPerson($person);
        if ($person->isAgent() && !isset($GLOBALS['DP_LOGIN_VIA_TOKEN'])) {
            // Announce if its an agent
            $this->setAgentIsAvailable($person);
            $this->sendLoginAlert($person, $request);
            $this->loginLog($request, $person);
            $this->broadcastAgentIsOnline($person);
        }

        $this->rememberMe($person);

        $this->deleteCookies();

        if ($login_validate_comments = App::getSession()->get('login_validate_comments')) {
            foreach ($login_validate_comments as $validate_info) {
                $comment = $this->em()->find($validate_info[0], $validate_info[1]);
                if (!$comment) {
                    continue;
                }

                $comment->status = 'validating';
                $this->em()->getConnection()->beginTransaction();

                try {
                    $this->em()->persist($comment);
                    $this->em()->flush();

                    $this->em()->getConnection()->commit();
                } catch (\Exception $e) {
                    $this->em()->getConnection()->rollBack();
                    throw $e;
                }
            }        // Form wasnt inputted (eg direct url)
            if (!$inputEmail || !$this->in->getString('password')) {
                if ($request->getMethod() == 'POST') {
                    $this->session->set('failed_to_login', true);
                    $this->session->save();
                }

                return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
            }

            App::getSession()->remove('login_validate_comments');
            App::getSession()->save();
        }

        $this->_doLoginSuccess();

        // Check if license expired
        $license = License::getLicense();
        if ($license->isPastExpireDate() && $person->isAdmin()) {
            return $this->redirect($this->generateUrl('admin_interface').'#/license');
        }

        if ($return) {
            return $this->redirect($return);
        }

        return $this->redirectRoute($this->routePrefix);
    }

    /**
     * @param Person $person
     */
    protected function rememberMe(Person $person)
    {
        if ($this->in->getBool('remember_me')) {
            $cookie = new \Symfony\Component\HttpFoundation\Cookie(
                'dpreme',
                $person->getId().'-'.$person->getRememberMeCookieCode(),
                // anyway we can't just set false for expire, so we will just set it every time to far distant future
                new \DateTime('2030-01-01 00:00:00'),
                null,
                null,
                Web::getRequestProtocol() == 'HTTPS' ? true : false,
                true
            );
            header('Set-Cookie: '.$cookie->__toString(), false); //eew, but since it's legacy it just should work
        }
    }

    /**
     * @param Request $request
     * @param Person  $person
     * @param bool    $success
     * @param string  $note
     */
    protected function loginLog(Request $request, Person $person, $success = true, $note = '')
    {
        $this->db->insert('login_log', [
            'person_id'    => $person->getId(),
            'area'         => defined('DP_INTERFACE') ? DP_INTERFACE : 'unknown',
            'is_success'   => $success ? 1 : 0,
            'ip_address'   => $request->getClientIp(),
            'hostname'     => @gethostbyaddr($request->getClientIp()) ?: '',
            'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
            'note'         => $note,
            'date_created' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Person $person
     * @param bool   $success
     */
    protected function sendLoginAlert(Person $person, Request $request, $success = true)
    {
        $prefName = sprintf('agent_notif.login_attempt%s.email', $success ? '' : '_fail');
        if ($person->getPref($prefName) && !$person->isDeleted()) {
            if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = $this->get('email.agent_viewmodel_factory')
                    ->createLoginAlertModel(
                        $request,
                        $this->session->getEntity()->getDateCreated(),
                        $success
                    );
                $this->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $message = $this->container->getMailer()->createMessage();
                $message->setTemplate(
                    'DeskPRO:emails_agent:login-alert.html.twig',
                    [
                        'success'   => $success,
                        'firstSeen' => $this->session->getEntity()->getDateCreated(),
                        'request'   => $request,
                    ]
                );
                $message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
                $this->container->getMailer()->send($message);
            }
        }
    }

    /**
     * @param Person $person
     */
    protected function broadcastAgentIsOnline(Person $person)
    {
        $this->get('event_dispatcher')->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.new-agent-online', [
                'agent_id'         => $person->getId(),
                'agent_name'       => $person->getDisplayName(),
                'agent_short_name' => $person->getDisplayContactShort(4),
                'picture_url'      => $person->getPictureUrl(10),
            ]
        ));
    }

    /**
     * @param Person $person
     */
    protected function setAgentIsAvailable(Person $person)
    {
        $this->session->set('active_status', 'available');
        if ($person->hasPerm('agent_chat.use')) {
            $this->session->set('is_chat_available', $person->getPref('agent.chat.is_available', 1));
        } else {
            $this->session->set('is_chat_available', 0);
        }
    }

    /**
     * @param array $cookies
     */
    protected function deleteCookies($cookies = ['dplogout', 'dp-guest-cache'])
    {
        foreach ($cookies as $cookie) {
            Cookie::makeDeleteCookie($cookie)->send();
        }
    }

    /**
     * @param Request $request
     */
    protected function handleLoginAttempt(Request $request)
    {
        if (isset($GLOBALS['DP_LOGIN_VIA_TOKEN'])) {
            return;
        }
        /** @var PersonRepository $personRepository */
        $personRepository = $this->em()->getRepository(Person::class);
        $attemptPerson    = $personRepository->findOneByEmail($this->in->getString('email'));
        if ($attemptPerson) {
            $this->sendLoginAlert($attemptPerson, $request, false);
            $this->loginLog($request, $attemptPerson, false);
        }
    }

    /**
     * @param Person $person
     *
     * @return Response
     */
    protected function handleIpSecurityCheck(Person $person)
    {
        if (!CheckWhitelistedIP::checkIP($this->getRequest(), $this->container, $person)) {
            return $this->render('AgentBundle:Login:whitelist-ip.html.twig', [
                'ip' => $this->getRequest()->getClientIp(),
            ]);
        }
    }

    public function _doLoginSuccess()
    {
        return;
    }

    /**
     * @return Result
     */
    public function authLocalInput()
    {
        $authResult = $this->auth_manager->authenticateFormLogin(
            $this->in->getString('email'),
            $this->in->getString('password')
        );

        // if we are using local auth in the user interface, and we fail, try agent form login sources as well
        if ('user' === $this->auth_manager->getInterface()) {
            if (!$authResult->isValid()) {
                $agentAuthManager = $this->auth_manager->cloneForInterface('agent');

                $authResult = $agentAuthManager->authenticateFormLogin(
                    $this->in->getString('email'),
                    $this->in->getString('password')
                );
            }
        }

        return $authResult;
    }

    //###########################################################################
    // Usersource auth
    //###########################################################################

    /**
     * @param $usersource_id
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return Response
     */
    public function authenticateAction($usersource_id)
    {
        $return = LegacyRequestUtils::readReturnParam($this->request);

        if ($usersource_test = $this->in->getBool(self::USERSOURCE_TEST)) {
            $this->session->setFlash(self::USERSOURCE_TEST, 1);
        }

        $usersource = $this->em()->find(Usersource::class, $usersource_id);
        if (!$usersource) {
            throw $this->createNotFoundException();
        }
        $adapter = $this->_initUserSourceAdapter($usersource, $this->in->getString('context'));

        //------------------------------
        // This needs to be an allowed adapter via settings
        // -----------------------------

        if (!$usersource_test && !$this->auth_manager->isUsableUsersource($usersource)) {
            throw $this->createNotFoundException('it is illegal to use this usersource in this context');
        }

        //------------------------------
        // Callback types require us to redirect
        //------------------------------

        $route_type = 'user';
        if (defined('DP_INTERFACE') && DP_INTERFACE == 'agent') {
            $route_type = 'agent';
        }
        // not needed now, might be needed in a future adapter
        //if ($usersource_test) {
        //	$route_type = $usersource->type;
        //}

        if ($adapter instanceof CallbackInterface) {
            $result = $adapter->authenticate();

            // The user is already logged in
            if ($result->isValid()) {
                $login_processor = new LoginProcessor($usersource, $result->getIdentity());
                $person          = $login_processor->getPerson();
                $person->setLastLoginAt();

                $this->em()->persist($person);
                $this->em()->flush();

                $this->_setupUsersourceSession($usersource, $person, $result);

                if ($this->in->getString('js_tell')) {
                    $return = $this->generateUrl($route_type.'_jstell_login', [
                        'jstell'         => $this->in->getString('js_tell'),
                        'security_token' => $this->session->getEntity()->generateSecurityToken('jstell'),
                        'usersource_id'  => $usersource_id,
                    ]);

                    return $this->redirect($return);
                }

                if ($this->session->get('auth_return')) {
                    $return = $this->session->get('auth_return');
                    $this->session->remove('auth_return');
                    $this->session->save();

                    return $this->redirect($return);
                } else {
                    return $this->redirectRoute($this->routePrefix);
                }

                // We expect a redirect to be rquired
            } elseif ($result->isRedirectRequired()) {
                if (!$return = LegacyRequestUtils::readReturnParam($this->request)) {
                    if ($return = $this->request->server->get('HTTP_REFERER')) {
                        if (false !== stripos($return, '/login')) {
                            $return = null;
                        }
                    } else {
                        $return = null;
                    }
                }
                $this->session->set('auth_return', $return);

                if ($this->in->getString('js_tell')) {
                    $return = $this->generateUrl($route_type.'_jstell_login', [
                        'jstell'         => $this->in->getString('js_tell'),
                        'security_token' => $this->session->getEntity()->generateSecurityToken('jstell'),
                        'usersource_id'  => $usersource_id,
                    ], true);
                    $this->session->set('auth_return', $return);
                }

                $this->session->save();

                $r = $this->redirect($result->getRedirectUrl());
                $r->headers->set(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, 'Yes');

                return $r;

                // Otherwise its an error
            } else {
                $this->session->setFlash('login_failed', true);

                return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
            }

            //------------------------------
            // Other types should return a result right away
            //------------------------------
        } else {
            $result = $adapter->authenticate();

            // Valid
            if ($result->isValid()) {
                $login_processor = new LoginProcessor($usersource, $result->getIdentity());
                $person          = $login_processor->getPerson();

                $this->_setupUsersourceSession($usersource, $person, $result);

                $return = LegacyRequestUtils::readReturnParam($this->request);
                if ($return) {
                    return $this->redirect($return);
                } else {
                    return $this->redirectRoute($this->routePrefix);
                }

                // Error, go back to login
            } else {
                $this->session->setFlash('login_failed', true);

                return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
            }
        }
    }

    /**
     * @param $usersource_id
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return Response
     */
    public function authenticateCallbackAction($usersource_id)
    {
        $return     = LegacyRequestUtils::readReturnParam($this->request);
        $usersource = $this->em()->find(Usersource::class, $usersource_id);

        if (!$usersource) {
            throw $this->createNotFoundException();
        }

        $usersource_test = $this->session->getFlash(self::USERSOURCE_TEST, []);
        if (!$usersource_test) {
            $usersource_test = $this->in->getBool(self::USERSOURCE_TEST);
        }

        $adapter = $this->_initUserSourceAdapter($usersource);

        $this->attachTestLoggerIfNecessary($usersource_test, $adapter);

        // It must be a callback type to be here, so if not redirect back to login
        if (!($adapter instanceof CallbackInterface)) {
            $this->session->setFlash('login_failed', true);

            return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
        }

        $adapter->setCallbackContext($_REQUEST);

        $result = $adapter->authenticate();

        // Valid
        if ($result->isValid()) {
            try {
                $login_processor = new LoginProcessor($usersource, $result->getIdentity(), $usersource_test);
                $person          = $login_processor->getPerson();
            } catch (\Exception $e) {
                return $this->createJsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            if ($usersource_test) {
                //--------------------------------------
                // test result
                //--------------------------------------
                $log = $this->getAdapterLog($adapter);

                return $this->render(
                    'DeskPRO:Auth:_sso_test_verified.html.twig', [
                        'person' => $person,
                        'log'    => $log,
                    ]
                );
            }

            $this->_setupUsersourceSession($usersource, $person, $result);

            if ($this->session->get('auth_return')) {
                $return = $this->session->get('auth_return');
                $this->session->remove('auth_return');
                $this->session->save();

                return $this->redirect($return);
            } else {
                return $this->redirectRoute($this->routePrefix);
            }

            // Error, go back to login
        } else {
            if ($usersource_test) {
                //--------------------------------------
                // test result
                //--------------------------------------
                $log = $this->getAdapterLog($adapter);

                return $this->render(
                    'DeskPRO:Auth:_sso_test_failed.html.twig', [
                        'log' => $log,
                    ]
                );
            }

            $this->session->setFlash('login_failed', true);

            return $this->redirectRoute($this->routePrefix.'_login', ['return' => $return]);
        }
    }

    /**
     * @param Usersource $usersource
     * @param string     $displayContext
     * @param string     $useInterface
     *
     * @return AdapterInterface
     */
    protected function _initUserSourceAdapter(Usersource $usersource, $displayContext = null, $useInterface = null)
    {
        /** @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $factory */
        $factory = $this->container->getSystemService('usersource_auth_adapter_factory');

        return $factory->getAuthAdapter($usersource, $displayContext, $useInterface);
    }

    //###########################################################################
    // Resetting passwords
    //###########################################################################

    /**
     * @param string  $_format
     * @param Request $request
     *
     * @throws \Exception
     * @throws null
     *
     * @return Response
     */
    public function sendResetPasswordAction($_format, Request $request)
    {
        $p = $this->session->getPerson();
        if ($p && !$p instanceof PersonGuest) {
            $this->ensureStandardRequestToken();
        } else {
            $this->ensureRequestToken('user_login');
        }

        $email = $this->in->getString('email');

        try {
            $check = new PasswordResetAbuseCheck($email, $request->getClientIp());
            $this->getContainer()->get('anti_abuse')->check($check);
        } catch (AntiAbuseException $e) {
            if ($_format == 'json') {
                return $this->createJsonResponse(['success' => 0, 'error' => 'lockout']);
            } else {
                $this->session->setFlash('failed_rate_limit', true);

                return $this->redirectRoute($this->routePrefix.'_login_resetpass', ['return' => LegacyRequestUtils::readReturnParam($request)]);
            }
        }

        if ($check->isCaptchaRecommended()) {
            $captcha = $this->container->getSystemObject('form_captcha', ['type' => 'user_reset_password']);
            if (!$captcha->validate()) {
                if ($_format == 'json') {
                    return $this->createJsonResponse(['success' => 1]);
                } else {
                    $this->session->setFlash('captcha_reset_error', true);

                    return $this->redirectRoute($this->routePrefix.'_login_resetpass', ['return' => LegacyRequestUtils::readReturnParam($request)]);
                }
            }
        }

        if (!$email || !StringEmail::isValueValid($email)) {
            return $this->redirectRoute('user_login_resetpass', ['inv' => 1]);
        }

        /** @var PersonRepository $personRepository */
        $personRepository = $this->em()->getRepository(Person::class);
        $person           = $personRepository->findOneByEmail($email);

        /** @var \Application\DeskPRO\EntityRepository\TmpData $rep */
        $rep = $this->em()->getRepository(TmpData::class);
        // hardcoded rate-limit for reset password request
        if (2 <= $rep->getCountByName('reset-password:'.DP_INTERFACE.':'.$person['id'], 30 * 60)) {
            return $_format == 'json'
                ? $this->createJsonResponse(['success' => 1])
                : $this->render($this->tplPrefix.':reset-password-sent.html.twig', [
                    'route_prefix' => $this->routePrefix,
                    'did_send'     => true,
                ]);
        }

        $is_invalid = false;
        if ($person && $person->is_deleted) {
            $is_invalid = true;
        }

        if (!$person || $is_invalid) {

            // If no user was found in our database, then the account might not have
            // been set up yet. For adapters that support it, we can still see if we
            // can be helpful and redirect to another source they exist in
            if (!$is_invalid) {
                $usersources = $this->auth_manager->getForgotPasswordUsersources();
                foreach ($usersources as $us) {
                    try {
                        $found = $us->findIdentityByInput($email);
                    } catch (\Exception $e) {
                        $found = null;
                    }
                    if ($found && $us->lost_password_url) {
                        $r = $this->redirect($us->lost_password_url);
                        $r->headers->set(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, 'Yes');

                        return $r;
                    }
                }
            }

            if ($this->request->isXmlHttpRequest()) {
                return $this->createJsonResponse(['error' => 'invalid_email']);
            }

            // Default is to just show standard message to not reveal if account exists
            return $this->render($this->tplPrefix.':reset-password-sent.html.twig', [
                'route_prefix' => $this->routePrefix,
            ]);
        }

        // If they dont have a password, this either means they're not a user yet,
        // but could also mean they registered through a usersource which means they might
        // need to use a different reset URL
        if (!$person->password) {
            // TODO: this needs to be fixed, with new userassoc finder, and needs to be checked in order
            /** @var PersonUsersourceAssocRepository $personUsersourceAssocRepository */
            $personUsersourceAssocRepository = $this->em()->getRepository(PersonUsersourceAssoc::class);
            $associations                    = $personUsersourceAssocRepository->getAssociationsForPerson($person);
            $us_names                        = [];

            foreach ($associations as $assoc) {
                $us_names[] = $assoc->usersource->getTitle();
                if ($assoc->usersource->lost_password_url) {
                    if ($this->request->isXmlHttpRequest()) {
                        return $this->createJsonResponse(['status' => 'usersource_redirect', 'usersource_name' => $assoc->usersource->getTitle(), 'url' => $assoc->usersource->lost_password_url]);
                    }

                    $r = $this->redirect($assoc->usersource->lost_password_url);
                    $r->headers->set(RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER, 'Yes');

                    return $r;
                }
            }

            // If reg is enabled, then resetting a password makes them a user (below)
            // otherwise we fail with a no account error
            if (!$this->container->getSetting('core.reg_enabled')) {
                if ($us_names) {
                    if ($this->request->isXmlHttpRequest()) {
                        return $this->createJsonResponse(['status' => 'usersource_no_reset', 'usersource_name' => implode(', ', $us_names)]);
                    }

                    // No other user sources for the user
                    return $this->render($this->tplPrefix.':reset-password-sent.html.twig', [
                        'route_prefix' => $this->routePrefix,
                        'did_send'     => false,
                    ]);
                }
            }
        }

        // Admins cant reset their password, but we dont want to reveal to this unknown user that we're an admin
        // Send an email instead
        if (!defined('DPC_IS_CLOUD')) {
            if ($person->can_admin && $person->is_agent && !$person->is_deleted) {
                $vars = [
                    'person' => $person,
                    'email'  => $email,
                ];

                $this->container->getTranslator()->setDefaultPersonContext($person);
                if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                    $viewModel = $this->get('email.agent_viewmodel_factory')
                        ->createAdminNoResetPasswordModel();
                    $this->get('email.email_sender')
                        ->send($viewModel, ['to' => $email]);
                } else {
                    $message = $this->container->getMailer()->createMessage();
                    $message->setTemplate('DeskPRO:emails_agent:admin-noreset-password.html.twig', $vars);
                    $message->setTo($email, $person->getDisplayName());
                    $this->container->getMailer()->send($message);
                }
                $this->container->getTranslator()->setDefaultPersonContext($person);

                if ($_format == 'json') {
                    return $this->createJsonResponse(['success' => 1]);
                }

                return $this->render($this->tplPrefix.':reset-password-sent.html.twig', [
                    'route_prefix' => $this->routePrefix,
                    'did_send'     => true,
                ]);
            }
        }

        // If they're still here, then we just send them through the normal DeskPRO reset procedure

        $name    = 'reset-password:'.DP_INTERFACE.':'.$person['id'];
        $tmpdata = TmpData::create('reset-password', ['person' => $person->id], '+1 day', $name);
        $this->em()->persist($tmpdata);
        $this->em()->flush();

        $this->container->getTranslator()->setDefaultPersonContext($person);
        if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            if ($person->isAgent()) {
                $resetUrl = $this->container->get('router')->generate(
                    'agent_login',
                    ['code' => $tmpdata->getCode()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } else {
                $resetUrl = $this->container->get('router')->generate(
                    'user_login_resetpass_newpass',
                    ['code' => $tmpdata->getCode()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }
            $viewModel = $this->get('email.user_viewmodel_factory')
                ->createResetPasswordModel($resetUrl);
            $emailSender = $this->get('email.email_sender');
            $this->container->getTranslator()->setTemporaryLanguage(
                $person->getLanguage(),
                function () use ($emailSender, $viewModel, $person) {
                    $emailSender->send($viewModel, ['to' => $person]);
                }
            );
        } else {
            $vars = [
                'code' => $tmpdata->getCode(),
            ];
            $message = $this->container->getMailer()->createMessage();
            $message->setTemplate('DeskPRO:emails_user:reset-password.html.twig', $vars);
            $message->setTo($email, $person->getDisplayName());
            $this->container->getTranslator()->setDefaultPersonContext($person);
            $this->container->getTranslator()->setTemporaryLanguage(
                $person->getLanguage(),
                function () use ($message) {
                    $message->prepare();
                }
            );
            $this->container->getMailer()->send($message);
        }

        if ($_format == 'json') {
            return $this->createJsonResponse(['success' => 1]);
        }

        $this->session->remove('auth_person_id');
        $this->session->save();

        return $this->render($this->tplPrefix.':reset-password-sent.html.twig', [
            'route_prefix' => $this->routePrefix,
            'did_send'     => true,
        ]);
    }

    //###########################################################################
    // agent-login
    //###########################################################################

    /**
     * @param Request $request
     * @param         $code
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return Response
     */
    public function authAgentLoginAction(Request $request, $code)
    {

        /** @var TmpDataRepository $tmpDataRepository */
        $tmpDataRepository = $this->em()->getRepository(TmpData::class);
        if (!$tmp = $tmpDataRepository->getByCode($code)) {
            throw $this->createNotFoundException();
        }

        $agent  = $this->container->getAgentData()->get($tmp->getData('agent_id'));
        $person = $this->em()->find(Person::class, $tmp->getData('person_id'));

        if (!$agent || !$agent->is_agent || !$agent->hasPerm('agent_people.login_as') || !$person || $person->is_agent) {
            throw $this->createNotFoundException();
        }

        $this->session->set('auth_person_id', $person->id);
        $this->session->set('dp_interface', DP_INTERFACE);
        $this->session->save();

        $this->deleteCookies();

        $note = sprintf(
            'Agent login by Admin #%d %s <%s>',
            $agent->getId(),
            $agent->getDisplayName(),
            $agent->getEmailAddress()

        );

        $this->loginLog(
            $request,
            $person,
            true,
            $note
        );

        // Log to activity log
        $this->db->insert('person_activity', [
            'person_id'    => $person->getId(),
            'action_type'  => 'agent_login_as',
            'date_created' => date('Y-m-d H:i:s'),
            'details'      => serialize([
                'agent_id'    => $agent->getId(),
                'agent_name'  => $agent->getDisplayName(),
                'agent_email' => $agent->getEmailAddress(),
            ]),
        ]);

        return $this->redirectRoute('user_profile');
    }

    /**
     * @param $code
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return Response
     */
    public function whitelistIpAction($code)
    {
        /** @var TmpDataRepository $tmpDataRepository */
        $tmpDataRepository = $this->em()->getRepository(TmpData::class);
        $tmp_data          = $tmpDataRepository->getByCode($code);
        if (!$tmp_data) {
            throw $this->createNotFoundException();
        }

        $person = $this->em()->find(Person::class, $tmp_data->getData('person_id'));
        if (!$person) {
            throw $this->createNotFoundException();
        }

        $data = $tmp_data->getData();

        $whitelist_ip = new WhiteListedIp();

        $whitelist_ip['person']     = $person;
        $whitelist_ip['ip_address'] = $data['ip'];

        $this->em()->persist($whitelist_ip);
        $this->em()->remove($tmp_data);
        $this->em()->flush();

        return $this->redirectRoute('agent');
    }

    //###########################################################################
    // Usersource SSO

    // This action should ONLY be used when logging in via the background (iframe)
    // Use the standard callback URL if user is to see the response of this
    //###########################################################################

    /**
     * @param $usersource_id
     *
     * @return Response|NotFoundHttpException
     */
    public function usersourceSsoAction($usersource_id)
    {
        // TODO: user auth_manager for this
        $interface = $this->getInterface();
        $source    = $this
            ->usersource_manager
            ->getAll()
            ->forInterface($interface)// TODO: will always be user interface since this is always a user URL
            ->withCapability(UsersourceInfo::CAPABILITY_SSO_JS)
            ->mustHaveId($usersource_id)
            ->getFirstOrNull();

        if (!$source) {
            throw new NotFoundHttpException();
        }

        $adapter = $this->_initUserSourceAdapter($source);

        if (!$adapter instanceof SsoLoginActionInterface) {
            return new NotFoundHttpException();
        }

        $usersourceTest = $this->session->getFlash(self::USERSOURCE_TEST, []);
        if (!$usersourceTest) {
            $usersourceTest = $this->in->getBool(self::USERSOURCE_TEST);
        }

        $this->attachTestLoggerIfNecessary($usersourceTest, $adapter);

        $result = $adapter->getSsoLoginActionResult($this);

        if ($result->isValid()) {
            $loginProcessor = new LoginProcessor($source, $result->getIdentity(), $usersourceTest);
            try {
                $person = $loginProcessor->getPerson(null, true);
            } catch (Exception $e) {
                $log = $this->getAdapterLog($adapter);

                $log .= "\n\n".$e->getMessage();

                return $this->render('DeskPRO:Auth:_sso_test_failed.html.twig', [
                        'log'            => $log,
                        'display_errors' => $result->getMessages('display_errors'),
                    ]
                );
            }

            if ($usersourceTest) {
                //--------------------------------------
                // test result
                //--------------------------------------
                $log = $this->getAdapterLog($adapter);

                return $this->render('DeskPRO:Auth:_sso_test_verified.html.twig', [
                        'person' => $person,
                        'log'    => $log,
                    ]
                );
            }

            //-----------------------------------
            // log the user in. if background sso, refresh the page.
            //-----------------------------------
            $this->_setupUsersourceSession($source, $person, $result);

            if ($adapter->isBackgroundSsoSimpleRefresh()) {
                return $this->render('DeskPRO:Auth:_sso_refresh.html.twig');
            }
        } else {
            //--------------------------------------
            // test result
            //--------------------------------------
            if ($usersourceTest) {
                $log = $this->getAdapterLog($adapter);

                return $this->render('DeskPRO:Auth:_sso_test_failed.html.twig', [
                        'log'            => $log,
                        'display_errors' => $result->getMessages('display_errors'),
                    ]
                );
            }
        }

        $return = LegacyRequestUtils::readReturnParam($this->request);
        if ($return) {
            return $this->redirect($return);
        } else {
            return $this->redirectRoute($this->routePrefix);
        }
    }

    /**
     * @param Usersource $usersource
     * @param Person     $person
     * @param Result     $result
     */
    protected function _setupUsersourceSession(Usersource $usersource, Person $person, Result $result)
    {
        $this->session->set('auth_person_id', $person['id']);
        $this->session->setFlash('is_from_login', 'yes');
        $this->session->set('dp_interface', DP_INTERFACE);
        $this->session->set('auth_usersource_id', $usersource->id);
        $this->session->set('auth_usersource_type', $usersource->source_type);
        $this->session->set('usersource_display_name', $usersource->getAdapter()->getDisplayName($result->getIdentity()->getRawData()));
        $this->session->set('usersource_display_link', $usersource->getAdapter()->getDisplayLink($result->getIdentity()->getRawData()));
        $this->session->save();

        App::setCurrentPerson($person);

        $this->deleteCookies();
    }

    /**
     * @return bool
     */
    protected function getInterface()
    {
        if (defined('DP_INTERFACE')) {
            return DP_INTERFACE;
        } else {
            return;
        }
    }

    /**
     * get current login lockout time.
     *
     * @param null $email
     *
     * @return int|mixed
     */
    protected function getLoginLockoutTime($email = null)
    {
        if (!$email) {
            return 0;
        }

        /** @var PersonRepository $personRepository */
        $personRepository = $this->em()->getRepository(Person::class);
        if (!$person = $personRepository->findOneByEmail($email)) {
            return 0;
        }

        $context = $person['is_agent'] ? 'agent' : 'user';

        // 0 if disabled
        if (!$this->settings->get($context.'.'.LoginRateLimitSettings::KEY.'.enabled')) {
            return 0;
        }

        /** @var LoginLogRepository $rep */
        $rep         = $this->em()->getRepository(LoginLog::class);
        $maxAttempts = $this->settings->get($context.'.'.LoginRateLimitSettings::KEY.'.'.'attempts');
        $checkTime   = $this->settings->get($context.'.'.LoginRateLimitSettings::KEY.'.'.'attempts_time');
        $lockTime    = $this->settings->get($context.'.'.LoginRateLimitSettings::KEY.'.'.'lock_time');

        return $rep->getLoginLockoutTime($person, $maxAttempts, $checkTime, $lockTime);
    }

    /**
     * @param $usersource_test
     * @param $adapter
     *
     * @return ArrayWriter|null
     */
    private function attachTestLoggerIfNecessary($usersource_test, $adapter)
    {
        if ($usersource_test && $adapter instanceof Loggable) {
            $arr_writer = new ArrayWriter();
            $logger     = new Logger();
            $arr_wr     = new ArrayWriter();
            $logger->addWriter($arr_wr);

            $logger->logDebug('Adapter: '.get_class($adapter));
            $logger->logDebug('--- Begin ---');

            if (!$adapter->getLogger()) {
                $adapter->setLogger($logger);

                return $arr_writer;
            } else {
                $adapter->getLogger()->addWriter($arr_writer);

                return $arr_writer;
            }
        }

        return;
    }

    /**
     * @param $adapter
     *
     * @return string
     */
    private function getAdapterLog($adapter)
    {
        if ($adapter instanceof Loggable) {
            if (!$logger = $adapter->getLogger()) {
                return '';
            }
            if (!$writer_chain = $logger->getWriterChain()) {
                return '';
            }
            $writers = $writer_chain->getWriters();
            $log     = '';
            foreach ($writers as $writer) {
                if ($writer instanceof ArrayWriter) {
                    $log .= $writer->getMessagesAsString();
                }
            }

            return $log;
        }

        return '';
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function em()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function db()
    {
        return $this->getContainer()->get('database_connection');
    }
}
