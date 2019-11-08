<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\PersonRegistrationType;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPublishController extends AbstractController
{
    protected function getAuthComponents(Request $request)
    {
        $lastUsername = $request->hasPreviousSession() ? $this->getSession()->get('last_username') : null;
        $captchaForm  = null;
        $abuseCheck   = new LoginAbuseCheck($lastUsername, $request->getClientIp());
        $abuseCheck->markAsCheckOnly();
        $this->getAntiAbuseService()->check($abuseCheck);
        if ($abuseCheck->isCaptchaRecommended()) {
            $captchaForm = $this->createForm(DpCaptchaType::class);
        }

        $person = $this->getPersonFactory()->createNewPerson();

        // FORM
        $registerForm = $this->createForm(PersonRegistrationType::class, $person, [
            'settings'              => $this->getBrandContainer()->getSettings(),
            'saved_form_subrequest' => $this->isSavedFormSubRequest($request),
            'action'                => $this->generateUrl('portal_user_registration'),
        ]);

        if ($request->isMethod('get') && $request->query->count()) {
            // to set form default values from request query
            $formOptions['validation_groups']             = false;
            $formOptions['csrf_double_submit_skip_check'] = true;
        }

        $registerForm->handleRequest($request);

        // pre-fill form values
        if ($request->isMethod('get') && $request->query->has('person_registration')) {
            // set default values
            // using the string constant to acquire data from query instead of Form::getName for BC
            $registerForm->submit($request->query->get('person_registration') ?: []);
            FormValidatorChecker::clearFormErrors($registerForm);
        }

        $formView = $registerForm->createView();

        return [
            'auth_manager'  => $this->get('dp_authentication_manager.user'),
            'last_username' => $lastUsername,
            'captcha_form'  => $captchaForm ? $captchaForm->createView() : null,
            'register_form' => $formView,
        ];
    }
}
