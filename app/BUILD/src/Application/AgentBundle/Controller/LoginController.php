<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\EntityRepository\ApiToken as ApiTokenRepository;
use Application\DeskPRO\EntityRepository\TmpData as TmpDataRepository;
use Application\DeskPRO\EntityRepository;
use Application\DeskPRO\HttpFoundation\LegacyRequestUtils;
use Application\DeskPRO\Usersource\Adapter\DeskproOauth2Proxy;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use Orb\Auth\DPOAuth2Proxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends \Application\UserBundle\Controller\LoginController
{
    /** @var string */
    protected $tplPrefix = 'AgentBundle:Login';

    /** @var string */
    protected $routePrefix = 'agent';

    /**
     * Handles showing the login form, and on POST handles login credentials
     * through the auth adapters.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $return = LegacyRequestUtils::readReturnParam($request);

        if ($this->loginViaToken()) {
            if ($return) {
                return $this->redirect($return);
            } else {
                return $this->redirectRoute('agent');
            }
        }

        $hasLoggedOut = $request->cookies->has('dp-recent-logout');
        if ($hasLoggedOut) {
            // remove recent logout cookie on redirect login
            $cookie = \Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie('dp-recent-logout');
            $cookie->send();
        }

        // SSO Automatic Redirecting

        if ($res = $this->checkAuthSystemForResponse($this->getAgentAuthSettings(), $hasLoggedOut)) {
            return $res;
        }

        // Already logged in
        if (($this->session->getPerson() && $this->session->getPerson()->is_agent)) {
            if ($return) {
                return $this->redirect($return);
            }

            return $this->redirectRoute('agent');
        }

        /*
         * If I am not already logged in
         * If `core.setup_initial` is not set (this is how we know if you're going to /start)
         * If there is only 1 user in the db
         * And if that user has is_user=false
         * auto-start a session for that user
         */
        if (!$this->settings->get('core.setup_initial')) {
            $persons = $this->em->getRepository(Person::class)->findBy(
                ['is_agent' => true, 'can_admin' => true],
                [],
                1
            );

            if (count($persons) === 1) {
                /** @var Person $person */
                $person = reset($persons);

                if ($person->getLabelManager()->hasLabel('not_user')) {
                    $this->session->invalidate();
                    $this->session->set('auth_person_id', $person['id']);
                    $this->session->set('dp_interface', DP_INTERFACE);
                    $this->session->setFlash('is_from_login', 'yes');
                    $this->session->save();
                    App::setCurrentPerson($person);

                    if ($return) {
                        return $this->redirect($return);
                    }
                }
            }
        }

        $hasDoneReset = false;

        if ($code = $this->in->getString('reset_code')) {
            /** @var TmpDataRepository $tmpDataRepository */
            $tmpDataRepository = $this->em->getRepository(TmpData::class);
            $codeData          = $tmpDataRepository->getByCode($code, 'reset-password');
            $person            = null;
            if ($codeData) {
                $person = $this->em->find(Person::class, $codeData->getData('person_id', 0));
            }

            if ($codeData and $person) {
                if ($this->in->getString('new_password')) {
                    $hasDoneReset = true;

                    $person->setPassword($this->in->getString('new_password'));
                    $this->db->executeUpdate(
                        "
                        UPDATE people
                        SET
                            is_user = 1,
                            password_scheme = 'bcrypt',
                            `password` = ?
                        WHERE id = ?
                    ",
                        [$person->getPassword(), $person->getId()]
                    );

                    /** @var ApiTokenRepository $apiTokenRepository */
                    $apiTokenRepository = $this->em->getRepository(ApiToken::class);
                    $token              = $apiTokenRepository->getTokenForPerson($person);
                    if ($token) {
                        $token->regenerateToken();
                        $this->em->persist($token);
                    }

                    $this->db->delete('tmp_data', ['id' => $codeData->getId()]);

                    // Delete old sessions for this user
                    $this->db->delete('sessions', ['person_id' => $person->getId()]);
                } else {
                    return $this->render(
                        'AgentBundle:Login:reset-password.html.twig',
                        [
                            'reset_code'   => $this->in->getString('reset_code'),
                            'route_prefix' => $this->routePrefix,
                        ]
                    );
                }
            } else {
                throw $this->createNotFoundException();
            }
        }

        $failedLoginName = $this->session->get('failed_login_name', false);
        if (!$failedLoginName) {
            //we are going to guess they want to login with last username
            $failedLoginName = $this->session->get('last_username', false);
        }
        $failedToLogin = false;
        if ($this->session->has('failed_to_login')) {
            $failedToLogin = $this->session->get('failed_to_login');
            $this->session->remove('failed_to_login');
            $this->session->save();
        }

        $logoBlob = null;
        if ($logoBlobId = $this->settings->get('agent.login_logo_blob_id')) {
            $logoBlob = $this->em->find(Blob::class, $logoBlobId);
        }

        $captchaView = null;

        $check = new LoginAbuseCheck($failedLoginName, $request->getClientIp());
        $check->markAsCheckOnly();
        $this->container->get('anti_abuse')->check($check);
        if ($check->isCaptchaRecommended()) {
            $captcha     = $this->createForm(DpCaptchaType::class);
            $captchaView = $captcha->createView();
        }

        $urlCorrections = $request->attributes->get('deskpro.url_corrector.corrections', []);
        $urlCorrections = array_combine($urlCorrections, $urlCorrections);
        $isToAdmin      = $return ? strpos($return, 'admin') !== false : false;

        return $this->render(
            'AgentBundle:Login:index.html.twig',
            [
                'lockout'           => $check->isLockoutRecommended() ? $check->getLockoutTime() : false,
                'authManager'       => $this->get('dp_authentication_manager.agent'),
                'return'            => $return,
                'route_prefix'      => $this->routePrefix,
                'logo_blob'         => $logoBlob,
                'has_logged_out'    => $hasLoggedOut,
                'has_done_reset'    => $hasDoneReset,
                'failed_to_login'   => $failedToLogin,
                'failed_login_name' => $failedLoginName,
                'timeout'           => $this->in->getBool('timeout'),
                'captcha'           => $captchaView,
                'render_forgot_pw'  => $this->in->getString('forgot') ?: false,
                'url_corrections'   => $urlCorrections,
                'is_to_admin'       => $isToAdmin,
                'didReset'          => $this->in->getBool('did_reset'),
            ]
        );
    }

    public function viewResetPasswordFormAction()
    {
        return $this->redirect($this->generateUrl('agent_login').'#reset-password');
    }

    public function preloadSourcesAction()
    {
        return $this->render('AgentBundle:Login:js-preload.html.twig');
    }

    public function browserRequirementsAction()
    {
        return $this->render('AgentBundle:Login:browser-requirements.html.twig');
    }

    /**
     * @return Response
     */
    public function minIEVersionAction()
    {
        return $this->render('AgentBundle:Login:min-ie-version.html.twig');
    }

    /**
     * @param Request $request
     * @param $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function authenticateCallbackDPOAuth2Action( Request $request, $provider)
    {
        // we lookup this usersource to see if it was installed. if it wasn't we return a 403
        /** @var EntityRepository\Usersource $repository */
        $repository = $this->em->getRepository('DeskPRO:Usersource');
        $userSourceList = $repository->getBySpecification([
            'type' =>           'agent',
            'source_type' =>    DeskproOauth2Proxy::class,
            'is_enabled' =>     true,
        ], $multiple = false);

        if (empty($userSourceList)) {
            throw $this->createAccessDeniedException("Unauthorized access");
        }
        /** @var Usersource $usersource */
        $usersource = array_pop($userSourceList);

        // verify request comes from the actual dp-oauth2-proxy:
        // the request contains an 'authentication token'parameter that can only be emitted by the dp-oauth2-proxy
        // ask dp-oauth2-proxy to validate this token, and if error return 403, else continue
        $proxyClient = DPOAuth2Proxy::fromContainer($this->container);
        if (! $proxyClient->authenticateRequest($request)) {
            throw $this->createAccessDeniedException("Unauthorized access");
        }

        //we receive the following information via headers:
        // X-Forwarded-Access-Token
        // X-Forwarded-Email
        // X-Forwarded-User
        // we don't need to use the X-Forwarded-Access-Token at the moment to retrieve more information
        // we create an JWT token and we add the following information: X-Forwarded-Email
        $tokenParams = $proxyClient->encodeToken($request);
        $tokenQueryString = http_build_query($tokenParams);

        // we response with a 302/303 to agent/login/authenticate-callback/{usersource_id}?jwt-token =  LoginController:authenticateCallbackAction
        //authenticate-callback
        $deskproUrl = rtrim($this->container->getSetting('core.deskpro_url'), '/');
        $redirectUrl = $deskproUrl . sprintf('/agent/login/authenticate-callback/%s?%s', $usersource->id, $tokenQueryString);
        return $this->redirect($redirectUrl, 302);
    }


    public function authAdminLoginAction(Request $request, $code)
    {
        /** @var TmpDataRepository $tmpDataRepository */
        $tmpDataRepository = $this->em->getRepository(TmpData::class);
        $tmpData           = $tmpDataRepository->getByCode($code);
        if (!$tmpData) {
            throw $this->createNotFoundException();
        }

        $admin  = $this->em->getRepository(Person::class)->find($tmpData->getData('admin_id'));
        $person = $this->em->getRepository(Person::class)->find($tmpData->getData('agent_id'));

        if (!$admin || !$admin->can_admin || !$person || !$person->isAgent()) {
            throw $this->createNotFoundException();
        }

        $this->session->invalidate();
        $this->session->set('auth_person_id', $person->getId());
        $this->session->set('dp_interface', DP_INTERFACE);
        $this->session->set('auth_by', $this->auth_manager->getAuthBy());
        $this->session->save();

        $this->deleteCookies();

        $note = sprintf(
            'Admin login by Admin #%d %s <%s>',
            $admin->getId(),
            $admin->getDisplayName(),
            $admin->getEmailAddress()
        );
        $this->loginLog($request, $person, true, $note);

        return $this->redirectRoute('agent');
    }
}
//Your email address has been banned.
