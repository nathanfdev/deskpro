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

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\ApiToken as ApiTokenRepository;
use Application\DeskPRO\EntityRepository\TmpData as TmpDataRepository;
use Application\DeskPRO\HttpFoundation\LegacyRequestUtils;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
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
        if ($has_logged_out) {
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
