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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\PortalBundle\Helper\PortalValidation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SavedFormController extends AbstractController
{
    /**
     * @Route("/saved-form/{auth_code}", name="saved_form_auto_submit", defaults={"auth_code":null})
     * @Security("is_granted('ROLE_USER')")
     */
    public function autoSubmitAction($auth_code = null, Request $request)
    {
        if ($auth_code) {
            $saved_form = $this->getFormSaver()->getByExternalCode($auth_code);
        } else {
            $saved_form = $this->getFormSaver()->getAutoSubmitSavedForm();
        }

        if (!$saved_form) {
            throw new NotFoundHttpException('this saved form does not exist, it may have expired');
        }

        $response = $this->submitSavedForm($saved_form, $request);

        return $response;
    }

    /**
     * TEMP route that proxies to validateAction below. Needed for a route used in legacy "ticket-new-validate-email.html.twig".
     *
     * @Route("/validate/new-ticket/{access_code}", name="user_validate_ticket")
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
     * @Route("/validate/{type}/{auth_code}", name="portal_validation")
     * @ParamConverter("saved_form", class="App:SavedForm", options={"auth_code" = "auth_code"})
     */
    public function validateAction(Request $request, $type, SavedForm $saved_form)
    {
        $email_address = $saved_form->getMetaDataValue('email');

        // make sure this is a valid request
        $this->checkEmailAddress($email_address);

        switch ($type) {
            case PortalValidation::REGISTRATION:
                // submitting the saved registration form will validate the user and email
                // there is no person yet to speak of
                return $this->submitSavedForm($saved_form, $request);
            case PortalValidation::COMMENT:
                $person = $this->getPersonToValidate($saved_form);
                $this->validateThisPerson($person, $email_address);
                $this->maybeAuthenticateThisPerson($person);

                return $this->submitSavedForm($saved_form, $request);
            case PortalValidation::ADD_EMAIL:
                $person = $saved_form->getPerson();
                $this->get('person_manipulator')->validatePerson($person);
                $this->maybeAuthenticateThisPerson($person);

                return $this->submitSavedForm($saved_form, $request);
            case PortalValidation::NEW_FEEDBACK:
                $person = $this->getPersonToValidate($saved_form);
                $this->validateThisPerson($person, $email_address);
                $this->maybeAuthenticateThisPerson($person);

                return $this->submitSavedForm($saved_form, $request);
            case PortalValidation::NEW_TICKET:
                $person = $this->getPersonToValidate($saved_form);
                $this->validateThisPerson($person, $email_address);

                return $this->submitSavedForm($saved_form, $request);
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
            // TODO: make a better error page
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
}
