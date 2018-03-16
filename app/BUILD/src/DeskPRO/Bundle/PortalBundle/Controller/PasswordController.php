<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\PasswordHistory;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Adapter\DeskPRO;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\PasswordResetAbuseCheck;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\PasswordResetRequestType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\PersonChangePasswordType;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The same controller code does both the "reset password" and "set password" urls. We differentate
 * by comparing matched route names.
 */
class PasswordController extends AbstractController
{
    const SET_PASSWORD_REDIRECT = 'set_password_redirect';

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

        $deskproUsersource = $this->getEm()->getRepository(Usersource::class)->findOneBy([
            'source_type' => DeskPRO::class,
            'type'        => 'user',
        ]);

        if (!$deskproUsersource->isEnabled()) {
            return $this->redirectToRoute('portal_login');
        }

        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('portal_home');
        }

        $form = $this->createForm(PasswordResetRequestType::class, ['email' => $request->get('email', '')]);
        $form->handleRequest($request);

        $renderError = false;
        if ($form->isValid()) {
            $this->runAntiAbuseCheck($request);

            $data  = $form->getData();
            $email = $data['email'];

            $personData = $this->getPersonDataService();
            $person     = $personData->getPersonForEmail($email);

            if ($person) {
                if ($person->isAgent() || $person->isAdmin()) {
                    // redirect to the /agent forgot password functionality
                    return $this->redirectToRoute('agent_login', ['forgot' => $email]);
                }

                // check if the reset code is not exist or was created more than 1 hour ago
                // if so, then create a new code and send a new message
                if ($personData->isPasswordResetReSendExpired($person)) {
                    // set the reset code
                    $validSeconds = $this->getBrandSetting('user.password_reset_code_time_limit', 18000);
                    $reset        = $personData->createPasswordReset($person, $validSeconds);

                    if ($isResetting) {
                        $this->get('portal_email_sender')->sendPasswordResetLink($person, $reset);
                    } else {
                        $this->get('portal_email_sender')->sendPasswordSetLink($person, $reset);
                    }
                }
            }

            $tpl = $isResetting ?
                'Theme:Password:password-reset-requested.html.twig' :
                'Theme:Password:set-password-requested.html.twig';

            return $this->renderThemeView(
                $tpl,
                [
                    'email'       => $email,
                    'breadcrumbs' => $this->getBreadcrumbGenerator()->buildPasswordReset($isResetting),
                    'page_title'  => $this->createPageTitle()->passwordReset($isResetting),
                ]
            );
        } elseif ($form->isSubmitted()) {
            $this->runAntiAbuseCheck($request);
            $renderError = true;
        }

        $tpl = $isResetting ?
            'Theme:Password:password-reset-request.html.twig' :
            'Theme:Password:set-password-request.html.twig';

        $check = new PasswordResetAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
        $check->markAsCheckOnly();
        $this->get('anti_abuse')->check($check);

        return $this->renderThemeView(
            $tpl,
            [
                'auth_manager' => $this->get('dp_authentication_manager.user'),
                'form'         => $form->createView(),
                'render_error' => $renderError,
                'breadcrumbs'  => $this->getBreadcrumbGenerator()->buildPasswordReset($isResetting),
                'page_title'   => $this->createPageTitle()->passwordReset($isResetting),
                'lockout'      => $check->isLockoutRecommended(),
                'lockout_time' => $check->getLockoutTime(true),
            ]
        );
    }

    /**
     * @Route("/login/reset-password/{code}", name="portal_reset_password_process")
     * @Route("/login/set-password/{code}", name="portal_set_password_process")
     */
    public function passwordResetAction(Request $request, $code, $_route)
    {
        // resetting or setting? we use diff templates/routes.
        $isResetting   = $_route === 'portal_reset_password_process';
        $valid_seconds = $this->getBrandSetting('user.password_reset_code_time_limit', 18000);

        $deskproUsersource = $this->getEm()->getRepository(Usersource::class)->findOneBy([
            'source_type' => DeskPRO::class,
            'type'        => 'user',
        ]);

        if (!$deskproUsersource->isEnabled()) {
            return $this->redirectToRoute('portal_login');
        }

        $valid = false;

        /* @var \Application\DeskPRO\Entity\Person $person */
        $reset = $this->getPersonDataService()->findPasswordReset($code);
        if ($reset && $reset['date_requested']->getTimestamp() > (time() - $valid_seconds)) {
            $valid = true;
        }

        if (!$valid) {
            $tpl = $isResetting ?
                'Theme:Password:password-reset-invalid-code.html.twig' :
                'Theme:Password:set-password-invalid-code.html.twig';

            return $this->renderThemeView(
                $tpl,
                [
                'breadcrumbs' => $this->getBreadcrumbGenerator()->buildPasswordReset($isResetting),
                'page_title'  => $this->createPageTitle()->passwordReset($isResetting),
            ]);
        }

        $person = $reset['person'];

        $form = $this->createForm(PersonChangePasswordType::class, $person, [
            'settings'                 => $this->getBrandContainer()->getSettings(),
            'require_current_password' => false,
        ]);

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

            if ($history) {
                $this->getEm()->persist($history);
            }

            $this->persistAndFlushEntity($person);

            $this->getPersonDataService()->clearPasswordReset($reset);

            $primary_email = $person->getPrimaryEmail();
            if ($primary_email) {
                $request->getSession()->set('last_username',  $primary_email->email);
            }

            // log the user in
            $this->get('person_manipulator')->authenticatePerson($person);

            $this->addFlash('success', $this->phrase(
                $isResetting ? 'portal.account.reset-password-success' : 'portal.account.set-password-success'
            ));

            if ($redirect = $request->getSession()->get(self::SET_PASSWORD_REDIRECT)) {
                return new RedirectResponse($redirect);
            }

            return $person->isAgent()
                ? $this->redirectToRoute('agent_login', ['did_reset' => 1])
                : $this->redirectToRoute('portal_login');
        } elseif ($form->isSubmitted()) {
            $this->runAntiAbuseCheck($request);
        }

        $tpl = $isResetting ?
            'Theme:Password:password-reset.html.twig' :
            'Theme:Password:set-password.html.twig';

        $person = $this->getCurrentPerson();

        return $this->renderThemeView(
            $tpl,
            [
                'auth_manager' => $this->get('dp_authentication_manager.user'),
                'form'         => $form->createView(),
                'breadcrumbs'  => $this->getBreadcrumbGenerator()->buildPasswordReset($isResetting),
                'page_title'   => $this->createPageTitle()->passwordReset($isResetting),
                'person'       => $person,
                'from_saved'   => $request->get('from-saved', false),
            ]
        );
    }

    protected function runAntiAbuseCheck(Request $request)
    {
        $check = new PasswordResetAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
        $check->setResponse($this->redirectToRoute('portal_reset_password', ['lockout' => 'reset']));
        $this->get('anti_abuse')->check($check);
    }
}
