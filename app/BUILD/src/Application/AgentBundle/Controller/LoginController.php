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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends \Application\UserBundle\Controller\LoginController
{
    /** @var string */
    protected $tpl_prefix = 'AgentBundle:Login';
    /** @var string */
    protected $route_prefix = 'agent';

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
        $return = LegacyRequestUtils::readReturnParam($this->request);

        if ($this->loginViaToken()) {
            if ($return) {
                return $this->redirect($return);
            } else {
                return $this->redirectRoute('agent');
            }
        }

        $has_logged_out = $this->in->checkIsset('o');

        //
        // SSO Automatic Redirecting
        //
        if ($res = $this->checkAuthSystemForResponse($this->getAgentAuthSettings(), $has_logged_out)) {
            return $res;
        }

        // Already logged in
        if (($this->session->getPerson() && $this->session->getPerson()->is_agent)) {
            if ($return) {
                return $this->redirect($return);
            }

            $url = $this->container->getBrandSetting('core.deskpro_url').'agent/';

            return $this->redirect($url);
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

        $has_done_reset = false;

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
                    $has_done_reset = true;

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
                            'route_prefix' => $this->route_prefix,
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

        $logo_blob = null;
        if ($logo_blob_id = $this->settings->get('agent.login_logo_blob_id')) {
            $logo_blob = $this->em->find(Blob::class, $logo_blob_id);
        }

        $captcha = null;

        $check = new LoginAbuseCheck($failedLoginName, $request->getClientIp());
        $check->markAsCheckOnly();
        $this->container->get('anti_abuse')->check($check);
        if ($check->isCaptchaRecommended()) {
            $captcha = $this->container->getSystemObject('form_captcha', ['type' => 'user_login']);
        }

        $url_corrections = $request->attributes->get('deskpro.url_corrector.corrections', []);
        $url_corrections = array_combine($url_corrections, $url_corrections);
        $is_to_admin     = $return ? strpos($return, 'admin') !== false : false;

        return $this->render(
            'AgentBundle:Login:index.html.twig',
            [
                'lockout'           => $check->isLockoutRecommended() ? $check->getLockoutTime() : false,
                'return'            => $return,
                'route_prefix'      => $this->route_prefix,
                'logo_blob'         => $logo_blob,
                'has_logged_out'    => $has_logged_out,
                'has_done_reset'    => $has_done_reset,
                'failed_to_login'   => $failedToLogin,
                'failed_login_name' => $failedLoginName,
                'timeout'           => $this->in->getBool('timeout'),
                'captcha'           => $captcha,
                'render_forgot_pw'  => $this->in->getString('forgot') ?: false,
                'url_corrections'   => $url_corrections,
                'is_to_admin'       => $is_to_admin,
            ]
        );
    }

    public function preloadSourcesAction()
    {
        return $this->render('AgentBundle:Login:js-preload.html.twig');
    }

    public function browserRequirementsAction()
    {
        return $this->render('AgentBundle:Login:browser-requirements.html.twig');
    }

    public function authAdminLoginAction(Request $request, $code)
    {
        /** @var TmpDataRepository $tmpDataRepository */
        $tmpDataRepository = $this->em->getRepository(TmpData::class);
        $tmpData           = $tmpDataRepository->getByCode($code);
        if (!$tmpData) {
            throw $this->createNotFoundException();
        }

        $agentDataService = $this->container->getAgentData();
        $admin            = $agentDataService->get($tmpData->getData('admin_id'));
        $person           = $agentDataService->get($tmpData->getData('agent_id'));

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
