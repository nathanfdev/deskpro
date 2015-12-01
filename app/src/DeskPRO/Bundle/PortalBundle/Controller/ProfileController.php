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
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Person\PersonValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

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
            'settings' => $this->getBrandContainer()->getSettings(),
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $event = new RegistrationAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
            $this->getAntiAbuseService()->check($event);
            // check if the person already has an account (or is a contact)
            if ($email = $person->getEmailAddress()) {
                if ($person_check = $this->get('data.person')->getPersonForEmail($email)) {
                    if ($person_check->isUser()) {
                        // this is an error, a registered user cannot register again
                       $form->get('primary_email')->addError(new FormError($this->phrase('portal.account.registration-email-already-exists')));
                    } else {
                        // contact, they should now get a "set password" email and a redirection
                        // set the reset code
                        $random = new UriSafeTokenGenerator();
                        $person_check->setPasswordResetCode($random->generateToken());
                        $person_check->setDatePasswordResetRequested(new \DateTime());
                        $this->persistAndFlushEntity($person_check);

                        $this->get('portal_email_sender')->sendPasswordSetLink($person_check);

                        return $this->redirectToRoute('portal_user_register_set_password', array(
                            'email' => $person_check->getPrimaryEmailAddress(),
                        ));
                    }
                }
            }
        }

        if ($form->isValid()) {
            $context = new CreatePersonContext('gateway.person');
            $this->getPersonFactory()->saveNewPerson($person, $context);
            $this->getEmailSender()->sendWelcomeEmail($person);

            // TODO core.email_validation is gone
            if (!$this->getBrandSetting('core.email_validation')) {
                $this->addFlash('success', $this->phrase('portal.flashes.user_registered'));
            } else {
                $this->addFlash('success', $this->phrase('portal.flashes.user_registered_must_verify'));
            }
            $request->getSession()->set('last_username', $person->getPrimaryEmail() ? $person->getPrimaryEmail()->getEmail() : '');

            return $this->redirectToRoute('portal_login');
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
     * @todo PersonEmailValidating is gone
     * @Security("is_granted('EDIT_PROFILE', user)")
     */
    public function editEmailsAction(Request $request)
    {
        $person = $this->getUser();

        // find emails awaiting validation
        $validating = $this->getEmailDataService()->getValidatingEmails($person);

        $add_email_form = null;
        $verify_url     = null;
        if ($person->isEmailValidated()) {

            //////////////////////////////////////////////////////////////////////////////////////////////
            // CHANGE PRIMARY EMAIL
            //////////////////////////////////////////////////////////////////////////////////////////////
            if ($email_id = $request->query->get('new_primary')) {
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
                $proposed_email_removal = $this->getRepo('DeskPRO:PersonEmail')->find($email_id);
                if ($proposed_email_removal->getPerson()->getId() == $person->getId()) {
                    if (!$proposed_email_removal->isPrimary()) { // cannot remove primary email
                        $person->removeEmail($proposed_email_removal);
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
                $new_email
            );
            $add_email_form->handleRequest($request);

            // quick and dirty custom validation to make sure a new email is not already validating
            // on another account
            if ($add_email_form->isSubmitted()) {
                if ($this->getRepo('DeskPRO:PersonEmailValidating')->getEmail($new_email->getEmail())) {
                    // this email validating already exists!
                    $add_email_form->addError(
                        new FormError(
                            'The email "'.$new_email->getEmail().' is already awaiting validation.'
                        )
                    );
                }
            }

            if ($add_email_form->isValid()) {
                $validating_email = new PersonEmailValidating();
                $validating_email->setEmail($new_email->getEmail());
                $validating_email->setPerson($person);
                $validating[] = $validating_email;
                $this->getEm()->persist($validating_email);
                $this->getEm()->flush();
                // TODO: validation
                //$this->get('portal_email_sender')->sendEmailConfirmationEmail($new_email);
                $this->addFlash('success', $this->phrase('portal.flashes.user_updated_emails'));

                return $this->redirectToRoute('portal_user_profile_emails');
            }
        } else {
            $verify_url = $this->get('person.portal_validator')->getResendLink(PersonValidator::TYPE_EMAIL_PRIMARY, $person->getPrimaryEmail());
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildProfileEmails();

        // make links for validating emails
        $validating_ui = array();
        if ($validating) {
            $person_validator = $this->get('person.portal_validator');
            foreach ($validating as $validating_email) {
                $validating_ui[$validating_email->getEmail()] = $person_validator->getResendLink(
                    PersonValidator::TYPE_EMAIL,
                    $validating_email,
                    null,
                    true
                );
            }
        }

        return $this->renderThemeView(
            'Theme:Portal:User/profile-emails.html.twig', array(
                'person'            => $person,
                'verify_url'        => $verify_url,
                'add_email_form'    => $add_email_form ? $add_email_form->createView() : null,
                'breadcrumbs'       => $breadcrumbs,
                'page_title'        => $this->createPageTitle()->profileEmails(),
                'validating_emails' => $validating_ui,
            )
        );
    }
}
