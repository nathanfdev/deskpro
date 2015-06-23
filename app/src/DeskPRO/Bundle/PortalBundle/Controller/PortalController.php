<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Person\PersonValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

class PortalController extends AbstractController
{
    /**
     * @Route("/", name="portal_index")
     * @PageHttpCache()
     */
    public function homeAction(Request $request)
    {
        return $this->renderThemeView('Theme:Portal:home.html.twig',
            array(
                'page_title' => $this->createPageTitle()->homepage()
            )
        );
    }

    /**
     * @Route("/login", name="portal_login")
     * @PageHttpCache()
     */
    public function loginAction(Request $request)
    {
        if ($this->getUser() instanceof Person) {
            return $this->redirectToRoute('portal_user_profile');
        }

        $saved_form_message = null;
        if ($saved_form = $this->getFormSaver()->getByExternalCode($request->get('saved_form'))) {
            // this person just filled out a form and is being asked to login to auto-submit it
            $saved_form_message = $this->getFormSaver()->getMessage($saved_form);
        }

        return $this->renderThemeView(
            'Theme:Portal:User/login.html.twig',
            array(
                'auth_manager'  => $this->get('dp_authentication_manager.user'),
                'login_error'   => $request->get('retry') == 'auth',
                'saved_form' => $saved_form,
                'saved_form_message' => $saved_form_message,
                'last_username' => $this->getSession()->get('last_username'),
                'reset_success' => $request->get('reset_success', 0),
                'breadcrumbs' => $this->getBreadcrumbGenerator()->buildLogin(),
                'page_title' => $this->createPageTitle()->loginPage()
            )
        );
    }

    /**
     * @Route("/reset-password", name="portal_reset_password")
     * @PageHttpCache()
     */
    public function passwordResetRequestAction(Request $request)
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('portal_index');
        }

        $form = $this->createForm('request_password_reset', array('email' => $request->get('email', '')));

        $form->handleRequest($request);

        $render_error = false;
        if ($form->isValid()) {
            $data  = $form->getData();
            $email = $data['email'];

            if ($person = $this->getPersonDataService()->getPersonForEmail($email)) {
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
                $this->get('new_mailer')->sendPasswordResetLink($person);
            }

            return $this->renderThemeView('Theme:Portal:User/password-reset-requested.html.twig', array(
                'email' => $email,
                'breadcrumbs' => $this->getBreadcrumbGenerator()->buildPasswordReset(),
                'page_title' => $this->createPageTitle()->passwordReset()
            ));
        } elseif ($form->isSubmitted()) {
            $render_error = true;
        }

        return $this->renderThemeView('Theme:Portal:User/password-reset-request.html.twig', array(
            'auth_manager' => $this->get('dp_authentication_manager.user'),
            'form'         => $form->createView(),
            'render_error' => $render_error,
            'breadcrumbs' => $this->getBreadcrumbGenerator()->buildPasswordReset(),
            'page_title' => $this->createPageTitle()->passwordReset()
        ));
    }

    /**
     * @Route("/reset-password/{password_reset_code}", name="portal_reset_password_process")
     */
    public function passwordResetAction(Request $request, $password_reset_code)
    {
        /** @var \Application\DeskPRO\Entity\Person $person */
        $person = $this->getPersonDataService()->getPersonForPasswordResetCode($password_reset_code);

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
            return $this->renderThemeView('Theme:Portal:User/password-reset-invalid-code.html.twig', array(
                'breadcrumbs' => $this->getBreadcrumbGenerator()->buildPasswordReset(),
                'page_title' => $this->createPageTitle()->passwordReset()
            ));
        }

        $form = $this->createForm('person_change_password', $person, array(
            'settings'                 => $this->getBrandContainer()->getSettings(),
            'require_current_password' => false,
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $person->setPasswordResetCode(null);
            $this->persistAndFlushEntity($person);

            $primary_email = $person->getPrimaryEmail();
            if ($primary_email) {
                $request->getSession()->set('last_username',  $primary_email->email);
            }

            return $this->redirectToRoute('portal_login', array('reset_success' => 1));
        }

        return $this->renderThemeView('Theme:Portal:User/password-reset.html.twig', array(
            'auth_manager' => $this->get('dp_authentication_manager.user'),
            'form'         => $form->createView(),
            'breadcrumbs' => $this->getBreadcrumbGenerator()->buildPasswordReset(),
            'page_title' => $this->createPageTitle()->passwordReset()
        ));
    }

    /**
     * @Route("/validate/{object_type}/{email_id}/{object_id}", name="portal_validation", defaults={"object_id":null})
     */
    public function validateAction(Request $request, $object_type, $email_id, $object_id)
    {
        switch($object_type) {
            case PersonValidator::TYPE_EMAIL:
                $this->getPersonValidator()->validateEmail($email_id);
                $this->addFlash('success', $this->phrase('portal.flashes.validated_email'));
                break;
        }

        if (!$this->getUser()) {
            // if the user is not logged in, send them to the login page with their email filled in
            $email = $this->getEmailDataService()->getEmail($email_id);
            $request->getSession()->set(
                'last_username',
               $email ? $email->getEmail() : ''
            );
            return $this->redirectToRoute('portal_login');
        }

        return $this->redirectToRoute('portal_index');
    }

    /**
     * @Route("/validate-send/{object_type}/{email_id}/{object_id}", name="portal_send_validation", defaults={"object_id":null})
     */
    public function resendValidationEmailAction(Request $request, $object_type, $email_id, $object_id)
    {
        switch($object_type) {
            case PersonValidator::TYPE_EMAIL:
                $this->getPersonValidator()->doResendLink(PersonValidator::TYPE_EMAIL, $email_id);
                $this->addFlash('success', $this->phrase('portal.flashes.sent_verification_email'));
                break;
        }

        return $this->redirectToRoute('portal_index');
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Person\PersonValidator
     */
    protected function getPersonValidator()
    {
        return $this->get('portal_person_validator');
    }
}
