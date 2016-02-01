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
namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\HttpFoundation\LegacyRequestUtils;
use Application\DeskPRO\Service\RateLimit;

class LoginController extends \Application\UserBundle\Controller\LoginController
{
    /** @var string */
    protected $tpl_prefix = 'AgentBundle:Login';
    /** @var string */
    protected $route_prefix = 'agent';

    /**
     * Handles showing the login form, and on POST handles login credentials
     * through the auth adapters.
     */
    public function indexAction()
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

            $url = App::getSetting('core.deskpro_url').'agent/';

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
            $persons = $this->em->getRepository('DeskPRO:Person')->findBy(array('is_agent' => true, 'can_admin' => true), array(), 1);

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
            $code_data = $this->em->getRepository('DeskPRO:TmpData')->getByCode($code, 'reset-password');
            $person    = null;
            if ($code_data) {
                $person = $this->em->find('DeskPRO:Person', $code_data->getData('person_id', 0));
            }

            if ($code_data and $person) {
                if ($this->in->getString('new_password')) {
                    $has_done_reset = true;

                    $person->setPassword($this->in->getString('new_password'));
                    $this->db->executeUpdate("
                        UPDATE people
                        SET
                            is_user = 1,
                            password_scheme = 'bcrypt',
                            `password` = ?
                        WHERE id = ?
                    ", array($person->password, $person->getId()));

                    $token = App::getEntityRepository('DeskPRO:ApiToken')->getTokenForPerson($person);
                    if ($token) {
                        $token->regenerateToken();
                        App::getOrm()->persist($token);
                    }

                    $this->db->delete('tmp_data', array('id' => $code_data->getId()));

                    // Delete old sessions for this user
                    $this->db->delete('sessions', array('person_id' => $person->getId()));
                } else {
                    return $this->render('AgentBundle:Login:reset-password.html.twig', array(
                        'reset_code'   => $this->in->getString('reset_code'),
                        'route_prefix' => $this->route_prefix,
                    ));
                }
            } else {
                throw $this->createNotFoundException();
            }
        }

        $failed_login_name = false;
        if ($this->session->has('failed_login_name')) {
            $failed_login_name = $this->session->get('failed_login_name');
            $this->session->remove('failed_login_name');
            $this->session->save();
        }

        $logo_blob = null;
        if ($logo_blob_id = $this->settings->get('agent.login_logo_blob_id')) {
            $logo_blob = $this->em->find('DeskPRO:Blob', $logo_blob_id);
        }

        $captcha = null;
        /** @var RateLimit $rateLimit */
        $rateLimit = $this->get(RateLimit::KEY);
        if ($rateLimit->isActionLimited(RateLimit::ACT_LOGIN)) {
            $captcha = $this->container->getSystemObject('form_captcha', array('type' => 'user_login'));
        }

        return $this->render('AgentBundle:Login:index.html.twig', array(
            'return'            => $return,
            'route_prefix'      => $this->route_prefix,
            'logo_blob'         => $logo_blob,
            'has_logged_out'    => $has_logged_out,
            'has_done_reset'    => $has_done_reset,
            'failed_login_name' => $failed_login_name,
            'timeout'           => $this->in->getBool('timeout'),
            'captcha'           => $captcha,
            'render_forgot_pw'  => $this->in->getString('forgot') ?: false,
        ));
    }

    public function preloadSourcesAction()
    {
        return $this->render('AgentBundle:Login:js-preload.html.twig');
    }

    public function browserRequirementsAction()
    {
        return $this->render('AgentBundle:Login:browser-requirements.html.twig');
    }

    public function authAdminLoginAction($code)
    {
        $tmp = $this->em->getRepository('DeskPRO:TmpData')->getByCode($code);
        if (!$tmp) {
            throw $this->createNotFoundException();
        }

        $admin  = $this->container->getAgentData()->get($tmp->getData('admin_id'));
        $person = $this->container->getAgentData()->get($tmp->getData('agent_id'));

        if (!$admin || !$admin->can_admin || !$person || !$person->is_agent) {
            throw $this->createNotFoundException();
        }

        $this->session->invalidate();
        $this->session->set('auth_person_id', $person->id);
        $this->session->set('dp_interface', DP_INTERFACE);
        $this->session->save();

        \Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie('dplogout')->send();
        \Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie('dp-guest-cache')->send();

        $this->db->insert('login_log', array(
            'person_id'    => $person->getId(),
            'area'         => 'agent',
            'is_success'   => 1,
            'ip_address'   => $this->getRequest()->getClientIp(),
            'hostname'     => @gethostbyaddr($this->getRequest()->getClientIp()) ?: '',
            'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
            'note'         => "Admin login by Admin #{$admin->id} {$admin->display_name} <{$admin->email_address}>",
            'date_created' => date('Y-m-d H:i:s'),
        ));

        return $this->redirectRoute('agent');
    }
}
