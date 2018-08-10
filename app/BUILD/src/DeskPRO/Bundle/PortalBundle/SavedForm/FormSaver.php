<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\SavedForm;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Repository\SavedFormRepository;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormSaver
{
    const AUTO_SUBMIT_SESSION_KEY = 'portal_form_saver_auto_submit';
    const SAVED_FORMS_SESSION_KEY = 'portal_form_saver_saved_forms';

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SavedFormRepository
     */
    private $saved_form_repo;

    /**
     * @var Session
     */
    private $session;

    public function __construct(UrlGeneratorInterface $generator, EntityManager $em, Session $session)
    {
        $this->generator       = $generator;
        $this->em              = $em;
        $this->saved_form_repo = $em->getRepository('DeskPRO\Bundle\AppBundle\Entity\SavedForm');
        $this->session         = $session;
    }

    /**
     * Get the SavedForm that should be auto submitted immediately.
     *
     * @return SavedForm|null
     */
    public function getAutoSubmitSavedForm()
    {
        if ($external_code = $this->session->get(static::AUTO_SUBMIT_SESSION_KEY)) {
            return $this->saved_form_repo->getByExternalCode($external_code);
        }

        return;
    }

    /**
     * Find a list of SavedForm objects that we should display to this user.
     *
     * @param Person $person
     *
     * @return SavedForm[]
     */
    public function getSavedForms(Person $person)
    {
        $saved_forms = [];

        $external_codes = $this->session->get(static::SAVED_FORMS_SESSION_KEY, []);
        if (!is_array($external_codes)) {
            $external_codes = [];
        }

        return $this->saved_form_repo->getForPerson($person, $external_codes);
    }

    /**
     * A shortcut to the repo method.
     *
     * @param $external_code
     *
     * @return SavedForm|null
     */
    public function getByExternalCode($external_code)
    {
        return $this->saved_form_repo->getByExternalCode($external_code);
    }

    /**
     * Meant to be called directly after a form was successfully submitted, but we need to redirect
     * the user to login first. This is not useful if you do not yet have a Person object it belongs to.
     *
     * Saves the form information and prepares the session for auto-submit. Returns the correct
     * redirect reponse that your controller should return immediately to auto-submit.
     *
     * @param string        $data_type the type of data being saved (feedback, ticket, etc) - a const of this class
     * @param Person        $person    the Person that needs to log in
     * @param FormInterface $form      the submitted form
     * @param Request       $request   the request that was used to submit the form
     *
     * @return RedirectResponse
     */
    public function saveFormForPersonLogin($data_type, Person $person, FormInterface $form, Request $request)
    {
        $data         = $request->request->all();
        $route        = $request->attributes->get('_route');
        $route_params = $request->attributes->get('_route_params');

        $saved_form = new SavedForm($data_type, SavedForm::INTENTION_LOGIN, $person);
        $saved_form->setFormData($data);
        $saved_form->setMetaData([
            'route'        => $route,
            'route_params' => $route_params,
        ]);

        // if we can, make it easier to login when prompted to login
        if ($email = $person->getEmailAddress()) {
            $request->getSession()->set('last_username',  $email);
        }

        $this->em->persist($saved_form);
        $this->em->flush();

        $this->declareAutoSubmit($saved_form);
        $this->appendToSavedForms($saved_form);

        return new RedirectResponse(
            $this->generator->generate('portal_login', [
                'saved_form' => $saved_form->getExternalCode(),
            ])
        );
    }

    /**
     * Simply saves a form and gives you the SavedForm object back (already persisted).
     *
     * These are usually NOT meant to be automaitcally submitted when the user logs in, because
     * they cannot log in yet (user registration for example) and will be dealt with outside
     * of the normal flow of forcing a user to login before submitting.
     *
     * @param string        $data_type the type of data being saved (feedback, ticket, etc) - a const of this class
     * @param FormInterface $form      the submitted form
     * @param Request       $request   the request that was used to submit the form
     * @param string|null   $name
     * @param string|null   $email
     * @param Person        $person    - optional, a Person object if we know it. most calls to this will be for email verification and we won't know the person directly though
     *
     * @return SavedForm
     */
    public function saveForm($data_type, FormInterface $form, Request $request, $email = null, $name = null, Person $person = null)
    {
        $data         = $request->request->all();
        $route        = $request->attributes->get('_route');
        $route_params = $request->attributes->get('_route_params');

        $saved_form = new SavedForm($data_type, SavedForm::INTENTION_VERIFY_EMAIL, $person); // may or may not be a person, but if there is saved it to the form
        $saved_form->setFormData($data);
        $saved_form->setMetaData([
            'route'        => $route,
            'route_params' => $route_params,
            'email'        => $email,
            'name'         => $name,
        ]);

        // if we can, make it easier to login when prompted to login
        if ($email) {
            $request->getSession()->set('last_username',  $email);
        } elseif ($person && $person->getPrimaryEmail()) {
            $request->getSession()->set('last_username',  $person->getEmailAddress());
        }

        $this->em->persist($saved_form);
        $this->em->flush();

        return $saved_form;
    }

    public function getMessage(SavedForm $saved_form)
    {
        return $saved_form->getMessage();
    }

    public function getDataType(SavedForm $saved_form)
    {
        return $saved_form->getDataType();
    }

    /**
     * Mark the $saved_form as completed. It deletes the saved form and removes
     * it from the session. This is called directly before we send a response
     * that will auto submit, as the data would no longer be needed.
     *
     * @param SavedForm $saved_form
     */
    public function markCompleted(SavedForm $saved_form)
    {
        // remove it from the auto submit key, if it is the active auto submit
        if ($auto_submit = $this->session->get(static::AUTO_SUBMIT_SESSION_KEY, null)) {
            if ($auto_submit == $saved_form->getExternalCode()) {
                $this->session->set(static::AUTO_SUBMIT_SESSION_KEY, null);
            }
        }

        // remove it from the in-session list
        $existing = $this->session->get(static::SAVED_FORMS_SESSION_KEY, []);
        if (!is_array($existing)) {
            $existing = [];
        }
        $new_existing = array_filter(
            $existing,
            function ($val) use ($saved_form) {
                return $val != $saved_form->getExternalCode();
            }
        );
        $this->session->set(static::SAVED_FORMS_SESSION_KEY, $new_existing);

        $this->em->remove($saved_form);
        $this->em->flush();
    }

    /**
     * When the SaveFormController is called with no external code, this saved form will be auto submitted.
     *
     * @param $saved_form
     */
    protected function declareAutoSubmit(SavedForm $saved_form)
    {
        $this->session->set(static::AUTO_SUBMIT_SESSION_KEY, $saved_form->getExternalCode());
    }

    /**
     * Add this saved form to a list of saved forms in this session.
     *
     * @param SavedForm $saved_form
     */
    protected function appendToSavedForms(SavedForm $saved_form)
    {
        $existing = $this->session->get(static::SAVED_FORMS_SESSION_KEY, []);

        if (!is_array($existing)) {
            $existing = [];
        }

        if (!in_array($saved_form->getExternalCode(), $existing)) {
            $existing[] = $saved_form->getExternalCode();
        }

        $this->session->set(static::SAVED_FORMS_SESSION_KEY, $existing);
    }
}
