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
namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\PasswordHistory;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\PasswordResetAbuseCheck;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

/**
 * The same controller code does both the "reset password" and "set password" urls. We differentate
 * by comparing matched route names.
 */
class PasswordController extends AbstractController
{
    /**
     * @Route("/login/reset-password", name="portal_reset_password")
     * @Route("/login/reset-password", name="user_login_resetpass")
     * @Route("/login/set-password", name="portal_set_password")
     * @PageHttpCache()
     */
    public function passwordResetRequestAction(Request $request, $_route)
    {
        // resetting or setting? we use diff templates/routes.
        $isResetting = $_route === 'portal_reset_password';

        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('portal_home');
        }

        $form = $this->createForm('request_password_reset', array('email' => $request->get('email', '')));

        $form->handleRequest($request);

        $render_error = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $this->runAntiAbuseCheck($request);

            $data  = $form->getData();
            $email = $data['email'];

            if ($person = $this->getPersonDataService()->getPersonForEmail($email)) {
                if ($person->isAgent() || $person->isAdmin()) {
                    // redirect to the /agent forgot password functionality
                    return $this->redirectToRoute('agent_login', array('forgot' => $email));
                }

                if (!$person->password) {
                    // TODO: this is copied from old portal, and we need to verify it works, moving on for now
                    $associations = $this->getRepo('DeskPRO:PersonUsersourceAssoc')
                        ->getAssociationsForPerson($person);

                    foreach ($associations as $assoc) {
                        if ($assoc->usersource->lost_password_url) {
                            return $this->redirect($assoc->usersource->lost_password_url);
                        }
                    }
                }

                // set the reset code
                $random = new UriSafeTokenGenerator();
                $person->setPasswordResetCode($random->generateToken());
                $person->setDatePasswordResetRequested(new \DateTime());

                $this->persistAndFlushEntity($person);
                if ($isResetting) {
                    $this->get('portal_email_sender')->sendPasswordResetLink($person);
                } else {
                    $this->get('portal_email_sender')->sendPasswordSetLink($person);
                }
            }

            $tpl = $isResetting ?
                'Theme:Password:password-reset-requested.html.twig' :
                'Theme:Password:set-password-requested.html.twig';

            return $this->renderThemeView(
                $tpl,
                array(
                    'email'       => $email,
                    'breadcrumbs' => $this->getBreadcrumbGenerator()->buildPasswordReset($isResetting),
                    'page_title'  => $this->createPageTitle()->passwordReset($isResetting),
                )
            );
        } elseif ($form->isSubmitted()) {
            $this->runAntiAbuseCheck($request);
            $render_error = true;
        }

        $tpl = $isResetting ?
            'Theme:Password:password-reset-request.html.twig' :
            'Theme:Password:set-password-request.html.twig';

        return $this->renderThemeView(
            $tpl,
            array(
                'auth_manager' => $this->get('dp_authentication_manager.user'),
                'form'         => $form->createView(),
                'render_error' => $render_error,
                'breadcrumbs'  => $this->getBreadcrumbGenerator()->buildPasswordReset($isResetting),
                'page_title'   => $this->createPageTitle()->passwordReset($isResetting),
            )
        );
    }

    /**
     * @Route("/login/reset-password/{code}", name="portal_reset_password_process")
     * @Route("/login/set-password/{code}", name="portal_set_password_process")
     */
    public function passwordResetAction(Request $request, $code, $_route)
    {
        // resetting or setting? we use diff templates/routes.
        $isResetting = $_route === 'portal_reset_password_process';

        /** @var \Application\DeskPRO\Entity\Person $person */
        $person = $this->getPersonDataService()->getPersonForPasswordResetCode($code);

        $valid = false;
        if ($person && $reset_requested_date = $person->getDatePasswordResetRequested()) {
            // find the cut-off datetime for an invalid time
            $valid_seconds = $this->getBrandSetting('user.password_reset_code_time_limit', 86400);
            $valid_time    = new \DateTime();
            $valid_time->sub(\DateInterval::createFromDateString(sprintf('%s seconds', $valid_seconds)));

            if ($reset_requested_date > $valid_time) {
                $valid = true;
            }
        }

        if (!$valid) {
            $tpl = $isResetting ?
                'Theme:Password:password-reset-invalid-code.html.twig' :
                'Theme:Password:set-password-invalid-code.html.twig';

            return $this->renderThemeView(
                $tpl,
                array(
                'breadcrumbs' => $this->getBreadcrumbGenerator()->buildPasswordReset($isResetting),
                'page_title'  => $this->createPageTitle()->passwordReset($isResetting),
            ));
        }

        $form = $this->createForm('person_change_password', $person, array(
            'settings'                 => $this->getBrandContainer()->getSettings(),
            'require_current_password' => false,
        ));

        $history = null;
        if ('POST' === $request->getMethod()) {
            if ($person->password && $person->password_scheme == 'bcrypt') {
                $history                  = new PasswordHistory();
                $history->person          = $person;
                $history->password_scheme = $person->password_scheme;
                $history->password        = $person->password;
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->runAntiAbuseCheck($request);

            $person->setPasswordResetCode(null);

            if ($history) {
                $this->getEm()->persist($history);
            }

            $this->persistAndFlushEntity($person);

            $primary_email = $person->getPrimaryEmail();
            if ($primary_email) {
                $request->getSession()->set('last_username',  $primary_email->email);
            }

            $params = $isResetting ?
                array('reset_success'        => 1) :
                array('set_password_success' => 1);

            return $this->redirectToRoute('portal_login', $params);
        } elseif ($form->isSubmitted()) {
            $this->runAntiAbuseCheck($request);
        }

        $tpl = $isResetting ?
            'Theme:Password:password-reset.html.twig' :
            'Theme:Password:set-password.html.twig';

        return $this->renderThemeView(
            $tpl,
            array(
                'auth_manager' => $this->get('dp_authentication_manager.user'),
                'form'         => $form->createView(),
                'breadcrumbs'  => $this->getBreadcrumbGenerator()->buildPasswordReset($isResetting),
                'page_title'   => $this->createPageTitle()->passwordReset($isResetting),
            )
        );
    }

    protected function runAntiAbuseCheck(Request $request)
    {
        $check = new PasswordResetAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
        $this->get('anti_abuse')->check($check);
    }
}
