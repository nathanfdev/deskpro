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
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\RegistrationAbuseCheck;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\PortalBundle\Helper\PortalValidation;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends AbstractController
{
    /**
     * @Route("/register", name="portal_user_registration")
     * @Route("/register", name="user_register")
     * @PageHttpCache()
     */
    public function registerAction(Request $request)
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('portal_home');
        }

        if (!$this->getBrandSetting('core.reg_enabled')) {
            return $this->redirectToRoute('portal_home');
        }

        //
        // Registration "intercept": to implement a registration intercept, don't use this
        // method of creating a person. Instead, make the form work with a PersonGuest,
        // and after the form is valid, use the PersonFactory to turn the guest into a
        // person. Please see NewTicketController to see how it does this exact process
        // to "intercept" new tickets.
        //

        $person = $this->getPersonFactory()->createNewPerson();

        // FORM
        $form = $this->createForm('person_registration', $person, array(
            'settings'              => $this->getBrandContainer()->getSettings(),
            'saved_form_subrequest' => $this->isSavedFormSubRequest($request),
            'action'                => $this->generateUrl('portal_user_registration'),
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $event = new RegistrationAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
            $this->getAntiAbuseService()->check($event);
            // check if the person already has an account (or is a contact)
            if ($email = $person->getEmailAddress()) {
                if ($person_check = $this->get('data.person')->getPersonForEmail($email)) {
                    if (!$person_check->isUser()) {
                        // contact, they should now get a "set password" email and a redirection
                        // set the reset code

                        $valid_seconds = $this->getBrandSetting('user.password_reset_code_time_limit', 18000);
                        $reset         = $this->getPersonDataService()->createPasswordReset($person_check, $valid_seconds);

                        $this->get('portal_email_sender')->sendPasswordSetLink($person_check, $reset);

                        return $this->redirectToRoute('portal_user_register_set_password', array(
                            'email' => $person_check->getPrimaryEmailAddress(),
                        ));
                    }
                }
            }
        }

        if ($form->isValid()) {
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

                return $this->redirectToRoute('portal_home');
            } else {
                // this is a normal web request, and we need email validation
                $saved_form = $this->getFormSaver()->saveForm(SavedForm::TYPE_REGISTER, $form, $request, $person->getEmailAddress(), $person->getDisplayName());
                $this->get('portal_validation')->sendVerificationEmail(PortalValidation::REGISTRATION, $saved_form);
                $this->addFlash('success', $this->phrase('portal.flashes.user_registered_must_verify'));
            }

            return $this->redirectToRoute('portal_home');
        }

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildRegistration();

        return $this->renderThemeView(
            'Theme:Portal:User/register.html.twig',
            array(
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbs,
                'page_title'  => $this->createPageTitle()->register(),
            )
        );
    }

    /**
     * @Route("/register/set-password", name="portal_user_register_set_password")
     */
    public function registerSetPasswordAction(Request $request)
    {
        return $this->renderThemeView(
            'Theme:Portal:User/send-set-password-email.html.twig',
            array(
                'email'       => $request->get('email', 'N/A'),
                'breadcrumbs' => $this->getBreadcrumbGenerator()->buildRegistration(),
                'page_title'  => $this->createPageTitle()->register(),
            )
        );
    }

    /**
     * @Route("/profile", name="portal_user_profile")
     * @Route("/profile", name="user_profile")
     * @Security("is_granted('EDIT_PROFILE', user)")
     */
    public function editAction(Request $request)
    {
        $person = $this->getUser();

        //
        // PROFILE
        //
        $profile_form = $this->createForm(
            'person_profile',
            $person,
            array(
                'settings' => $this->getBrandContainer()->getSettings(),
            )
        );
        $profile_form->handleRequest($request);
        if ($profile_form->isValid()) {
            $this->getEm()->flush();
            $this->addFlash('success', $this->phrase('portal.flashes.user_updated_profile'));

            return $this->redirectToRoute('portal_user_profile');
        }

        //
        // PASSWORD
        //
        $password_form = $this->createForm('person_change_password', $person, array(
            'settings' => $this->getBrandContainer()->getSettings(),
        ));
        if ('POST' === $request->getMethod()) {
            $history = null;
            if ($person->password && $person->password_scheme == 'bcrypt') {
                $history                  = new PasswordHistory();
                $history->person          = $person;
                $history->password_scheme = $person->password_scheme;
                $history->password        = $person->password;
            }
            $password_form->handleRequest($request);
            if ($password_form->isValid()) {
                if ($history) {
                    $this->getEm()->persist($history);
                }
                $this->getEm()->flush();
                $this->addFlash('success', $this->phrase('portal.flashes.user_changed_password'));

                return $this->redirectToRoute('portal_user_profile');
            }
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildProfile();

        return $this->renderThemeView(
            'Theme:Portal:User/profile.html.twig', array(
                'person'        => $person,
                'profile_form'  => $profile_form->createView(),
                'password_form' => $password_form->createView(),
                'breadcrumbs'   => $breadcrumbs,
                'page_title'    => $this->createPageTitle()->profile(),
            )
        );
    }
    /**
     * @Route("/profile/emails", name="portal_user_profile_emails")
     *
     * @Security("is_granted('EDIT_PROFILE', user)")
     */
    public function editEmailsAction(Request $request)
    {
        $person = $this->getUser();

        $add_email_form = null;
        if ($person->isEmailValidated()) {

            //////////////////////////////////////////////////////////////////////////////////////////////
            // CHANGE PRIMARY EMAIL
            //////////////////////////////////////////////////////////////////////////////////////////////
            if ($email_id = $request->query->get('new_primary')) {
                /** @var \Application\DeskPRO\Entity\PersonEmail $proposed_new_primary_email */
                $proposed_new_primary_email = $this->getRepo('DeskPRO:PersonEmail')->find($email_id);
                if ($proposed_new_primary_email->getPerson()->getId() == $person->getId()) {
                    $person->setPrimaryEmail($proposed_new_primary_email);
                    $this->getEm()->flush();
                    $this->addFlash('success', $this->phrase('portal.flashes.user_changed_primary_email'));

                    return $this->redirectToRoute('portal_user_profile_emails');
                }
            }

            //////////////////////////////////////////////////////////////////////////////////////////////
            // REMOVE EMAIL
            //////////////////////////////////////////////////////////////////////////////////////////////
            if ($email_id = $request->query->get('remove_email')) {
                /** @var \Application\DeskPRO\Entity\PersonEmail $proposed_email_removal */
                $proposed_email_removal = $this->getRepo('DeskPRO:PersonEmail')->find($email_id);
                if ($proposed_email_removal->getPerson()->getId() == $person->getId()) {
                    if (!$proposed_email_removal->isPrimary()) { // cannot remove primary email
                        $person->removeEmailAddressId($proposed_email_removal->getId());
                        $this->getEm()->remove($proposed_email_removal);
                        $this->getEm()->flush();
                        $this->addFlash(
                            'success',
                            $this->phrase(
                                'portal.flashes.user_removed_an_email',
                                array('email' => $proposed_email_removal->email)
                            )
                        );

                        return $this->redirectToRoute('portal_user_profile_emails');
                    }
                }
            }

            //////////////////////////////////////////////////////////////////////////////////////////////
            // NEW EMAIL
            //////////////////////////////////////////////////////////////////////////////////////////////
            $new_email      = new PersonEmail();
            $add_email_form = $this->createForm(
                'deskpro_person_email',
                $new_email,
                ['action' => $this->generateUrl('portal_user_profile_emails')]
            );
            $add_email_form->handleRequest($request);
            if ($add_email_form->isValid()) {
                if ($this->isSavedFormSubRequest($request)) {
                    // this is coming from a validtion link, so we can actually save the email now
                    $this->get('user_rule_processor')->newEmail($person, $new_email);
                    $this->getEm()->persist($new_email);
                    $new_email->person = $person;
                    $new_email->setIsValidated(true);
                    $this->getCurrentPerson()->addEmail($new_email);
                    $this->getEm()->flush();
                    $this->addFlash('success', $this->phrase('portal.flashes.user_add_email_verified'));

                    return $this->redirectToRoute('portal_user_profile_emails');
                } else {
                    $this->getEm()->detach($new_email);
                    // valid email, but we need email validation before adding it
                    $saved_form = $this->getFormSaver()->saveForm(SavedForm::TYPE_REGISTER, $add_email_form, $request, $new_email->getEmail(), $person->getDisplayName(), $person);
                    $this->get('portal_validation')->sendVerificationEmail(PortalValidation::ADD_EMAIL, $saved_form, false);
                    $this->addFlash('success', $this->phrase('portal.flashes.user_add_email_verify'));

                    return $this->redirectToRoute('portal_user_profile_emails');
                }
            }
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildProfileEmails();

        return $this->renderThemeView(
            'Theme:Portal:User/profile-emails.html.twig', array(
                'person'         => $person,
                'add_email_form' => $add_email_form ? $add_email_form->createView() : null,
                'breadcrumbs'    => $breadcrumbs,
                'page_title'     => $this->createPageTitle()->profileEmails(),
            )
        );
    }
}
