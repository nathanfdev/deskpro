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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\PasswordHistory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Notifications\NewRegistrationNotification;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\RegistrationAbuseCheck;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\PersonChangePasswordType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\PersonEditProfileType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\PersonRegistrationType;
use DeskPRO\Bundle\PortalBundle\Helper\PortalValidation;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProfileController.
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/register", name="portal_user_registration")
     * @Route("/register", name="user_register")
     * @PageHttpCache()
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function registerAction(Request $request)
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('portal_home');
        }

        if (!$this->getBrandSetting('core.reg_enabled')) {
            return $this->redirectToRoute('portal_home');
        }

        // Registration "intercept": to implement a registration intercept, don't use this
        // method of creating a person. Instead, make the form work with a PersonGuest,
        // and after the form is valid, use the PersonFactory to turn the guest into a
        // person. Please see NewTicketController to see how it does this exact process
        // to "intercept" new tickets.

        $person = $this->getPersonFactory()->createNewPerson();

        // FORM
        $form = $this->createForm(PersonRegistrationType::class, $person, [
            'settings'              => $this->getBrandContainer()->getSettings(),
            'saved_form_subrequest' => $this->isSavedFormSubRequest($request),
            'action'                => $this->generateUrl('portal_user_registration'),
        ]);

        if ($request->isMethod('get') && $request->query->count()) {
            // to set form default values from request query
            $formOptions['validation_groups']             = false;
            $formOptions['csrf_double_submit_skip_check'] = true;
        }

        $form->handleRequest($request);

        // pre-fill form values
        if ($request->isMethod('get') && $request->query->has('person_registration')) {
            // set default values
            // using the string constant to acquire data from query instead of Form::getName for BC
            $form->submit($request->query->get('person_registration') ?: []);
            FormValidatorChecker::clearFormErrors($form);
        }

        if ($request->isMethod('post')) {
            if ($form->isSubmitted()) {
                // check if the person already has an account (or is a contact)
                if ($email = $person->getEmailAddress()) {
                    /** @var Person $personCheck */
                    if ($personCheck = $this->get('data.person')->getPersonForEmail($email)) {
                        // uncomment this conditional if the "set password" email should only be sent to accounts
                        // that cannot login. accounts that get here that can login are given a form error instead.
                        //if (!$personCheck->isUser()) {
                            // contact, they should now get a "set password" email and a redirection
                            // set the reset code

                        $validSeconds = $this->getBrandSetting('user.password_reset_code_time_limit', 18000);
                        $reset        = $this->getPersonDataService()->createPasswordReset($personCheck, $validSeconds);

                        $this->get('portal_email_sender')->sendPasswordSetLink($personCheck, $reset);

                        return $this->redirectToRoute('portal_user_register_set_password', [
                                'email' => $personCheck->getPrimaryEmailAddress(),
                            ]);
                        //}
                    }
                }
            }

            if ($form->isValid()) {
                $event = new RegistrationAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
                $event->setResponse($this->redirectToRoute('portal_user_registration'));
                $this->getAntiAbuseService()->check($event);

                if ($this->isSavedFormSubRequest($request)) {
                    // this is coming from the validation controller, so this time we actually want to save the user
                    $request->getSession()->set(
                        'last_username',
                        $person->getPrimaryEmail() ? $person->getPrimaryEmail()->getEmail() : ''
                    );
                    $this->get('person_manipulator')->validatePerson($person, $person->getEmailAddress());
                    $context = new CreatePersonContext(Person::CREATED_WEB_PERSON);
                    $this->getPersonFactory()->saveNewPerson($person, $context);
                    $this->getEmailSender()->sendWelcomeEmail($person);
                    $this->get('person_manipulator')->authenticatePerson($person);
                    $this->addFlash('success', $this->phrase('portal.flashes.user_registered_verified_authenticated'));

                    $notify = new NewRegistrationNotification($person);
                    $notify->send();

                    return $this->redirectToRoute('portal_home');
                } else {
                    // this is a normal web request, and we need email validation
                    $savedForm = $this->getFormSaver()->saveForm(SavedForm::TYPE_REGISTER, $form, $request, $person->getEmailAddress(), $person->getDisplayName());
                    $this->get('portal_validation')->sendVerificationEmail(PortalValidation::REGISTRATION, $savedForm);
                    $this->addFlash('success', $this->phrase('portal.flashes.user_registered_must_verify'));
                }

                return $this->redirectToRoute('portal_home');
            }
        }

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildRegistration();

        $event = new RegistrationAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
        $event->markAsCheckOnly();
        $this->getAntiAbuseService()->check($event);

        return $this->renderThemeView(
            'Theme:Portal:User/register.html.twig',
            [
                'form'         => $form->createView(),
                'lockout'      => $event->isLockoutRecommended(),
                'lockout_time' => $event->getLockoutTime(true),
                'breadcrumbs'  => $breadcrumbs,
                'page_title'   => $this->createPageTitle()->register(),
            ]
        );
    }

    /**
     * @Route("/register/set-password", name="portal_user_register_set_password")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function registerSetPasswordAction(Request $request)
    {
        return $this->renderThemeView(
            'Theme:Portal:User/send-set-password-email.html.twig',
            [
                'email'       => $request->get('email', 'N/A'),
                'breadcrumbs' => $this->getBreadcrumbGenerator()->buildRegistration(),
                'page_title'  => $this->createPageTitle()->register(),
            ]
        );
    }

    /**
     * @Route("/profile/disabled", name="portal_user_disabled")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function disabledAction(Request $request)
    {
        $person = $this->getUser();
        if (!$person || !$person->is_disabled) {
            return $this->redirectToRoute('portal_user_profile');
        }

        return $this->renderThemeView(
            'Theme:Portal:User/user_disabled.html.twig', [
                'person' => $person,
            ]
        );
    }

    /**
     * @Route("/profile", name="portal_user_profile")
     * @Route("/profile", name="user_profile")
     * @Security("is_granted('EDIT_PROFILE', user)")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request)
    {
        $person = $this->getUser();

        // PROFILE

        $profileForm = $this->createForm(PersonEditProfileType::class, $person, [
            'settings' => $this->getBrandContainer()->getSettings(),
        ]);

        $profileForm->handleRequest($request);
        if ($profileForm->isValid()) {
            $this->getEm()->flush();
            $this->addFlash('success', $this->phrase('portal.flashes.user_updated_profile'));

            return $this->redirectToRoute('portal_user_profile');
        }

        // PASSWORD

        $passwordForm = $this->createForm(PersonChangePasswordType::class, $person, [
            'settings' => $this->getBrandContainer()->getSettings(),
        ]);

        if ('POST' === $request->getMethod()) {
            $history = null;
            if ($person->password && $person->password_scheme == 'bcrypt') {
                $history                  = new PasswordHistory();
                $history->person          = $person;
                $history->password_scheme = $person->password_scheme;
                $history->password        = $person->password;
            }

            $passwordForm->handleRequest($request);
            if ($passwordForm->isValid()) {
                if ($history) {
                    $this->getEm()->persist($history);
                }
                $this->getEm()->flush();
                $this->addFlash('success', $this->phrase('portal.flashes.user_changed_password'));

                return $this->redirectToRoute('portal_user_profile');
            }
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildProfile();

        return $this->renderThemeView(
            'Theme:Portal:User/profile.html.twig', [
                'person'        => $person,
                'profile_form'  => $profileForm->createView(),
                'password_form' => $passwordForm->createView(),
                'breadcrumbs'   => $breadcrumbs,
                'page_title'    => $this->createPageTitle()->profile(),
            ]
        );
    }

    /**
     * @Route("/profile/emails", name="portal_user_profile_emails")
     *
     * @Security("is_granted('EDIT_PROFILE', user)")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function editEmailsAction(Request $request)
    {
        $person = $this->getUser();

        $addEmailForm  = null;
        $pendingEmails = [];
        if ($person->isConfirmed()) {

            //////////////////////////////////////////////////////////////////////////////////////////////
            // CHANGE PRIMARY EMAIL
            //////////////////////////////////////////////////////////////////////////////////////////////
            if ($emailId = $request->query->getInt('new_primary')) {
                /** @var \Application\DeskPRO\Entity\PersonEmail $proposedNewPrimaryEmail */
                $proposedNewPrimaryEmail = $this->getRepo('DeskPRO:PersonEmail')->find($emailId);
                if ($proposedNewPrimaryEmail->getPerson()->getId() == $person->getId()) {
                    $person->setPrimaryEmail($proposedNewPrimaryEmail);
                    $this->getEm()->flush();
                    $this->addFlash('success', $this->phrase('portal.flashes.user_changed_primary_email'));

                    return $this->redirectToRoute('portal_user_profile_emails');
                }
            }

            //////////////////////////////////////////////////////////////////////////////////////////////
            // REMOVE EMAIL
            //////////////////////////////////////////////////////////////////////////////////////////////
            if ($emailId = $request->query->getInt('remove_email')) {
                /** @var \Application\DeskPRO\Entity\PersonEmail $proposedEmailRemoval */
                $proposedEmailRemoval = $this->getRepo('DeskPRO:PersonEmail')->find($emailId);
                if ($proposedEmailRemoval->getPerson()->getId() == $person->getId()) {
                    if (!$proposedEmailRemoval->isPrimary()) { // cannot remove primary email
                        $person->removeEmailAddressId($proposedEmailRemoval->getId());
                        $this->getEm()->remove($proposedEmailRemoval);
                        $this->getEm()->flush();
                        $this->addFlash(
                            'success',
                            $this->phrase(
                                'portal.flashes.user_removed_an_email',
                                ['email' => $proposedEmailRemoval->email]
                            )
                        );

                        return $this->redirectToRoute('portal_user_profile_emails');
                    }
                }
            }

            //////////////////////////////////////////////////////////////////////////////////////////////
            // NEW EMAIL
            //////////////////////////////////////////////////////////////////////////////////////////////
            $newEmail     = new PersonEmail();
            $addEmailForm = $this->createForm(PersonEmailType::class, $newEmail, [
                'action'      => $this->generateUrl('portal_user_profile_emails'),
                'email_label' => 'Email',
            ]);
            $addEmailForm->handleRequest($request);
            if ($addEmailForm->isValid()) {
                if ($this->isSavedFormSubRequest($request)) {
                    // this is coming from a validtion link, so we can actually save the email now
                    $this->get('user_rule_processor')->newEmail($person, $newEmail);
                    $this->getEm()->persist($newEmail);
                    $newEmail->person = $person;
                    $newEmail->setIsValidated(true);
                    $this->getCurrentPerson()->addEmail($newEmail);
                    $this->getEm()->flush();
                    $this->addFlash('success', $this->phrase('portal.flashes.user_add_email_verified'));

                    return $this->redirectToRoute('portal_user_profile_emails');
                } else {
                    $this->getEm()->detach($newEmail);
                    // valid email, but we need email validation before adding it
                    $savedForm = $this->getFormSaver()->saveForm(SavedForm::TYPE_REGISTER, $addEmailForm, $request, $newEmail->getEmail(), $person->getDisplayName(), $person);
                    $this->get('portal_validation')->sendVerificationEmail(PortalValidation::ADD_EMAIL, $savedForm, false);
                    $this->addFlash('success', $this->phrase('portal.flashes.user_add_email_verify'));

                    return $this->redirectToRoute('portal_user_profile_emails');
                }
            }

            //////////////////////////////////////////////////////////////////////////////////////////////
            // PENDING EMAIL ADDRESSES
            //////////////////////////////////////////////////////////////////////////////////////////////
            $savedForms = $this->getRepo(SavedForm::class)->findBy([
                'person'         => $person,
                'intention_type' => SavedForm::INTENTION_VERIFY_EMAIL,
            ]);
            /** @var SavedForm $savedForm */
            foreach ($savedForms as $savedForm) {
                $formData = $savedForm->getFormData();
                if (isset($formData['person_email']['email'])) {
                    $pendingEmails[$savedForm->getId()] = $formData['person_email']['email'];
                }
            }
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildProfileEmails();

        return $this->renderThemeView(
            'Theme:Portal:User/profile-emails.html.twig', [
                'person'         => $person,
                'pending_emails' => $pendingEmails,
                'add_email_form' => $addEmailForm ? $addEmailForm->createView() : null,
                'breadcrumbs'    => $breadcrumbs,
                'page_title'     => $this->createPageTitle()->profileEmails(),
            ]
        );
    }

    /**
     * @Route("/profile/emails/resend/{id}", name="portal_user_profile_emails_resend_validation")
     *
     * @Security("is_granted('EDIT_PROFILE', user)")
     *
     * @param SavedForm $savedForm
     *
     * @return RedirectResponse
     */
    public function resendValidationAction(SavedForm $savedForm)
    {
        if ($savedForm->getPerson() != $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to access this email address');
        }

        $this->addFlash('success', $this->phrase('portal.flashes.user_resend_email_verify'));

        $this->get('portal_validation')->sendVerificationEmail(PortalValidation::ADD_EMAIL, $savedForm, false);

        return $this->redirectToRoute('portal_user_profile_emails');
    }

    /**
     * @Route("/profile/emails/remove_pending/{id}", name="portal_user_profile_emails_remove_pending")
     *
     * @Security("is_granted('EDIT_PROFILE', user)")
     *
     * @param SavedForm $savedForm
     *
     * @return RedirectResponse
     */
    public function removePendingAction(SavedForm $savedForm)
    {
        if ($savedForm->getPerson() != $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to access this email address');
        }

        $formData     = $savedForm->getFormData();
        $emailAddress = '';
        if (isset($formData['person_email']['email'])) {
            $emailAddress = $formData['person_email']['email'];
        }

        $this->addFlash('success', $this->phrase('portal.flashes.user_add_email_verify', ['email' => $emailAddress]));

        $this->getEm()->remove($savedForm);
        $this->getEm()->flush();

        return $this->redirectToRoute('portal_user_profile_emails');
    }
}
