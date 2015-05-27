<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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
        $this->generator = $generator;
        $this->em = $em;
        $this->saved_form_repo = $em->getRepository('DeskPRO\Bundle\AppBundle\Entity\SavedForm');
        $this->session = $session;
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

        return null;
    }

    /**
     * Find a list of SavedForm objects that we should display to this user.
     *
     * @param Person $person
     * @return SavedForm[]
     */
    public function getSavedForms(Person $person)
    {
        $saved_forms = array();

        $external_codes = $this->session->get(static::SAVED_FORMS_SESSION_KEY, array());
        if (!is_array($external_codes)) {
            $external_codes = array();
        }

        return $this->saved_form_repo->getForPerson($person, $external_codes);
    }

    /**
     * A shortcut to the repo method
     *
     * @param $external_code
     * @return SavedForm|null
     */
    public function getByExternalCode($external_code)
    {
        return $this->saved_form_repo->getByExternalCode($external_code);
    }

    /**
     * Meant to be called directly after a form was successfully submitted, but we need to redirect
     * the user to login first.
     *
     * Saves the form information and prepares the session for auto-submit. Returns the correct
     * redirect reponse that your controller should return immediately to auto-submit.
     *
     * @param Person $person the Person that needs to log in
     * @param FormInterface $form the submitted form
     * @param Request $request the request that was used to submit the form
     * @return RedirectResponse
     */
    public function saveFormForPerson(Person $person, FormInterface $form, Request $request)
    {
        $data = $request->request->all();
        $route = $request->attributes->get('_route');
        $route_params = $request->attributes->get('_route_params');

        $saved_form = new SavedForm($person);
        $saved_form->setFormData($data);
        $saved_form->setMetaData(array(
            'route' => $route,
            'route_params' => $route_params
        ));

        $this->em->persist($saved_form);
        $this->em->flush($saved_form);

        $this->declareAutoSubmit($saved_form);
        $this->appendToSavedForms($saved_form);

        $this->session->getFlashBag()->add('error', 'You must login to submit this form.'); // TODO: more info? translation?

        return new RedirectResponse($this->generator->generate('portal_login'));
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
        $existing = $this->session->get(static::SAVED_FORMS_SESSION_KEY, array());
        if (!is_array($existing)) {
            $existing = array();
        }
        $new_existing = array_filter(
            $existing,
            function ($val) use ($saved_form) {
                return $val != $saved_form->getExternalCode();
            }
        );
        $this->session->set(static::SAVED_FORMS_SESSION_KEY, $new_existing);

        $this->em->remove($saved_form);
        $this->em->flush($saved_form);
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
        $existing = $this->session->get(static::SAVED_FORMS_SESSION_KEY, array());

        if (!is_array($existing)) {
            $existing = array();
        }

        if (!in_array($saved_form->getExternalCode(), $existing)) {
            $existing[] = $saved_form->getExternalCode();
        }

        $this->session->set(static::SAVED_FORMS_SESSION_KEY, $existing);
    }
}
