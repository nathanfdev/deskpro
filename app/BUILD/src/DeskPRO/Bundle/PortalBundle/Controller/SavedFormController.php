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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\EmailGateway\Runner;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\PortalBundle\Helper\PortalValidation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SavedFormController extends AbstractController
{
    /**
     * @Route("/saved-form/{auth_code}", name="saved_form_auto_submit", defaults={"auth_code":null})
     * @Security("is_granted('ROLE_USER')")
     */
    public function autoSubmitAction($auth_code, Request $request)
    {
        if ($auth_code) {
            $saved_form = $this->getFormSaver()->getByExternalCode($auth_code);
        } else {
            $saved_form = $this->getFormSaver()->getAutoSubmitSavedForm();
        }

        if (!$saved_form) {
            throw new NotFoundHttpException('this saved form does not exist, it may have expired');
        }

        if ($saved_form->getIntentionType() == SavedForm::INTENTION_VERIFY_EMAIL) {
            throw $this->createAccessDeniedException('this saved form can not be submitted');
        }

        $response = $this->submitSavedForm($saved_form, $request);

        return $response;
    }

    /**
     * TEMP route that proxies to validateAction below. Needed for a route used in legacy "ticket-new-validate-email.html.twig".
     *
     * @Route("/validate/new-ticket/auth/{access_code}", name="user_validate_ticket")
     *
     * @deprecated
     */
    public function ticketValidateAction($access_code)
    {
        $auth_code = $access_code;

        return $this->redirectToRoute('portal_validation', [
            'type'      => PortalValidation::NEW_TICKET,
            'auth_code' => $auth_code,
        ]);
    }

    /**
     * @Route("/validate/new-ticket-email/auth/{auth_code}", name="user_validate_ticketemail")
     */
    public function ticketEmailValidateAction($auth_code)
    {
        if (!$tmp = $this->getEm()->getRepository(TmpData::class)->getByCode($auth_code)) {
            throw new NotFoundHttpException();
        }

        if ($tmp->getType() !== 'newticket_email_validate') {
            throw new NotFoundHttpException();
        }

        $this->getEm()->remove($tmp);
        $this->getEm()->flush();

        $source = $this->getEm()->find('DeskPRO:EmailSource', $tmp->getData('email_source_id', 0));
        if (!$source) {
            throw new NotFoundHttpException();
        }

        $person_email = $this->getEm()->find('DeskPRO:PersonEmail', $tmp->getData('person_email_id', 0));
        if (!$person_email || !$person_email->person) {
            throw new NotFoundHttpException();
        }

        $this->validateThisPerson($person_email->person, $person_email->getEmail());

        $source['status']     = 'inserted';
        $source['error_code'] = null;

        $runner = new Runner();
        $runner->executeSource($source);

        $this->addFlash('success', $this->phrase('portal.flashes.ticket_created'));

        return $this->redirectToRoute('portal_home');
    }

    /**
     * @Route("/validate/usersource-email/auth/{tmp_auth}", name="user_validate_usersource_email")
     */
    public function validateUsersourceEmailAction(Request $request, $tmp_auth)
    {
        if (!$usersource = $this->get('usersource_identity_saver')->fetchUsersource($tmp_auth)) {
            throw new NotFoundHttpException();
        }

        if (!$identity = $this->get('usersource_identity_saver')->fetchIdentity($tmp_auth)) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(PersonEmailType::class);
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var \Application\DeskPRO\Entity\PersonEmail $email */
            $email         = $form->getData();
            $email_address = $email->getEmail();
            // create a NEW tmp data and hold this old tmp_data_auth in it
            // (for security reasons, we dont want to use it again for step 2 in "validateUsersourceEmailAfterClickAction").
            $new_tmp = TmpData::create('usersource_email_verification', [
                'usersource_tmp_auth' => $tmp_auth,
                'email_address'       => $email_address,
            ]);
            $this->persistAndFlushEntity($new_tmp);
            // fire an email with a link to validate
            $verify_url = $this->generateUrl('user_validate_usersource_email_2', ['tmp_auth' => $new_tmp->getAuth()], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->get('portal_validation')->sendUsersourceEmailValidation($email_address, $verify_url);
            $this->addFlash('success', $this->phrase('portal.flashes.usersource_new_add_email_verify'));

            return $this->redirectToRoute('portal_home');
        }

        return $this->renderThemeView('Theme:Portal:User/usersource-set-email.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/validate/usersource-email-clicked/auth/{tmp_auth}", name="user_validate_usersource_email_2")
     */
    public function validateUsersourceEmailAfterClickAction(Request $request, $tmp_auth)
    {
        /** @var \Application\DeskPRO\Entity\TmpData $tmp_data */
        if (!$tmp_data = $this->getRepo('DeskPRO:TmpData')->findOneBy(['auth' => $tmp_auth])) {
            throw new NotFoundHttpException();
        }

        $usersource_tmp_auth = $tmp_data->getData('usersource_tmp_auth');
        $email               = $tmp_data->getData('email_address');

        if (!$usersource = $this->get('usersource_identity_saver')->fetchUsersource($usersource_tmp_auth)) {
            throw new NotFoundHttpException();
        }

        if (!$identity = $this->get('usersource_identity_saver')->fetchIdentity($usersource_tmp_auth)) {
            throw new NotFoundHttpException();
        }

        $this->loginAndAuthenticateTmpUsersource($usersource_tmp_auth, $email);

        $this->addFlash('success', $this->phrase('portal.flashes.usersource_new_add_email_verified'));

        $this->removeUsedTmpData($usersource_tmp_auth, $tmp_data);

        return $this->redirectToRoute('portal_home');
    }

    /**
     * @Route("/validate/{type}/{auth_code}", name="portal_validation")
     *
     * @param Request $request
     * @param string  $type
     * @param string  $auth_code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validateAction(Request $request, $type, $auth_code)
    {
        $savedForm = $this->getEm()->getRepository(SavedForm::class)->findOneBy([
            'auth_code' => $auth_code,
        ]);
        if (!$savedForm) {
            // handle the case when a user click email verification link twice
            if ($type === PortalValidation::REGISTRATION) {
                $emailAddress = $request->query->get('email');
                if ($emailAddress && is_string($emailAddress)) {
                    $email = $this->getEm()->getRepository(PersonEmail::class)->findOneBy([
                        'email' => $emailAddress,
                    ]);

                    if ($email && $email->isValidated()) {
                        $this->addFlash('success', $this->phrase('portal.flashes.user_registered_verified'));

                        return $this->redirectToRoute('portal_home');
                    } else {
                        return $this->renderThemeView('Theme:Error:error_custom.html.twig', [
                            'error_title' => 'portal.account.link-expired',
                        ]);
                    }
                }
            }

            throw $this->createNotFoundException();
        }

        $emailAddress = $savedForm->getMetaDataValue('email');

        // make sure this is a valid request
        $this->checkEmailAddress($emailAddress);

        switch ($type) {
            case PortalValidation::REGISTRATION:
                // submitting the saved registration form will validate the user and email
                // there is no person yet to speak of
                return $this->submitSavedForm($savedForm, $request);
            case PortalValidation::COMMENT:
                $person = $this->getPersonToValidate($savedForm);
                $this->validateThisPerson($person, $emailAddress);
                $this->maybeAuthenticateThisPerson($person);

                return $this->submitSavedForm($savedForm, $request);
            case PortalValidation::ADD_EMAIL:
                $person = $savedForm->getPerson();
                $this->get('person_manipulator')->validatePerson($person);
                $this->maybeAuthenticateThisPerson($person);

                return $this->submitSavedForm($savedForm, $request);
            case PortalValidation::NEW_FEEDBACK:
                $person = $this->getPersonToValidate($savedForm);
                $this->validateThisPerson($person, $emailAddress);
                $this->maybeAuthenticateThisPerson($person);

                return $this->submitSavedForm($savedForm, $request);
            case PortalValidation::NEW_TICKET:
                $person = $this->getPersonToValidate($savedForm);
                $this->validateThisPerson($person, $emailAddress);

                return $this->submitSavedForm($savedForm, $request);
            default:
                break;
        }
    }

    /**
     * Validate a person, and the email they came here with.
     *
     * @param Person $person
     * @param null   $email_address
     */
    private function validateThisPerson(Person $person, $email_address = null)
    {
        $em = $this->getEm();

        $this->get('person_manipulator')->validatePerson($person, $email_address);

        $em->persist($person);
        $to_flush = [$person];

        if ($email_address) {
            $person_email = $person->getEmailByAddress($email_address);
            $em->persist($person_email);
            $to_flush[] = $person_email;
        }

        $em->flush($to_flush);
    }

    private function maybeAuthenticateThisPerson(Person $person)
    {
        $this->get('person_manipulator')->authenticatePerson($person);
    }

    private function getPersonToValidate(SavedForm $saved_form)
    {
        if ($email_address = $saved_form->getMetaDataValue('email')) {
            // we need to know if the email in the saved form is already attached to a person
            if ($person = $this->getExistingPersonByEmailAddress($email_address)) {
                return $person;
            }
        }

        // the saved form knew who this perosn was already, so return that person
        if ($person = $saved_form->getPerson()) {
            if ($email_address && !$person->hasEmailAddress($email_address)) {
                // we know that nobody has this email address, or it would have been caught
                // above. so we need to add it to the person on the saved form.
                $person->addEmailAddress($email_address);
            }

            return $person;
        }

        // the person does not exist, so we need to create one, and the email is already verified at this point
        $context = new CreatePersonContext(Person::CREATED_WEB_PERSON);
        if ($name = $saved_form->getMetaDataValue('name')) {
            $context->setName($name);
        }
        $person = $this->getPersonFactory()->createPersonByEmail($email_address, $context);

        return $person;
    }

    private function checkEmailAddress($email_address)
    {
        $authenticated_person = $this->getCurrentPerson();
        if ($authenticated_person instanceof PersonGuest) {
            return true; // this request is not authenticated, move on
        }

        if ($authenticated_person->hasEmailAddress($email_address)) {
            return true; // the authenticated person owns this email already, so this is an ok request
        }

        if ($person = $this->getExistingPersonByEmailAddress($email_address)) {
            // the authenticated user doesn't have this email, but it belongs to someone else
            // this is an edge case where we just throw a 403.
            throw new AccessDeniedException();
        }
    }

    /**
     * @param $email_address
     *
     * @return Person|null
     */
    private function getExistingPersonByEmailAddress($email_address)
    {
        $person_data = $this->getPersonDataService();
        $person      = $person_data->getPersonForEmail($email_address);

        return $person;
    }

    /**
     * @param $usersource_tmp_auth
     * @param $tmp_data
     */
    private function removeUsedTmpData($usersource_tmp_auth, $tmp_data)
    {
        $usersource_tmp_data = $this->getEm()->getRepository('DeskPRO:TmpData')->findOneBy(['auth' => $usersource_tmp_auth]);
        $this->getEm()->remove($tmp_data);
        $this->getEm()->remove($usersource_tmp_data);
        $this->getEm()->flush([$tmp_data, $usersource_tmp_data]);
    }

    /**
     * @param $tmp_auth
     * @param $email
     */
    private function loginAndAuthenticateTmpUsersource($tmp_auth, $email)
    {
        $usersource = $this->get('usersource_identity_saver')->fetchUsersource($tmp_auth);
        $identity   = $this->get('usersource_identity_saver')->fetchIdentity($tmp_auth);

        $processor = new LoginProcessor($usersource, $identity);
        $person    = $processor->getPerson($email);

        // run our rules on the new email for this new person
        $this->get('user_rule_processor')->newEmail($person, $person->getPrimaryEmail());

        $this->validateThisPerson($person);
        $this->maybeAuthenticateThisPerson($person);
    }
}
