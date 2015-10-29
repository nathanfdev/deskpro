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
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Person\PersonValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalController extends AbstractController
{
    /**
     * @Route("/", name="portal_home")
     * @Route("/", name="user")
     * @PageHttpCache()
     */
    public function homeAction(Request $request)
    {
        return $this->renderThemeView('Theme:Portal:home.html.twig',
            array(
                'page_title' => $this->createPageTitle()->homepage(),
            )
        );
    }

    /**
     * @Route("/login", name="portal_login")
     * @Route("/login", name="user_login")
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

        $captcha_form  = null;
        $last_username = $this->getSession()->get('last_username');
        $abuse_check   = new LoginAbuseCheck($last_username, $request->getClientIp());
        $abuse_check->markAsCheckOnly();
        $this->getAntiAbuseService()->check($abuse_check);
        if ($abuse_check->isCaptchaRecommended()) {
            $captcha_form = $this->createForm('deskpro_captcha');
        }

        return $this->renderThemeView(
            'Theme:Portal:User/login.html.twig',
            array(
                'auth_manager'         => $this->get('dp_authentication_manager.user'),
                'login_captcha_failed' => $request->get('retry') == 'captcha',
                'login_error'          => $request->get('retry') == 'auth',
                'lockout_error'        => $request->get('lockout') == 'auth',
                'saved_form'           => $saved_form,
                'saved_form_message'   => $saved_form_message,
                'captcha_form'         => $captcha_form ? $captcha_form->createView() : null,
                'last_username'        => $last_username,
                'reset_success'        => $request->get('reset_success', 0),
                'set_password_success' => $request->get('set_password_success', 0),
                'breadcrumbs'          => $this->getBreadcrumbGenerator()->buildLogin(),
                'page_title'           => $this->createPageTitle()->loginPage(),
            )
        );
    }

    /**
     * @Route("/validate/{object_type}/{email_id}/{object_id}", name="portal_validation", defaults={"object_id":null})
     */
    public function validateAction(Request $request, $object_type, $email_id, $object_id)
    {
        switch ($object_type) {
            case PersonValidator::TYPE_EMAIL:
                $this->getPersonValidator()->validateEmail($email_id, true);
                $this->addFlash('success', $this->phrase('portal.flashes.validated_email'));
                break;
            case PersonValidator::TYPE_EMAIL_PRIMARY:
                $this->getPersonValidator()->validateEmail($email_id);
                $this->addFlash('success', $this->phrase('portal.flashes.validated_email'));
                break;
            case PersonValidator::TYPE_FEEDBACK:
                if ($this->getPersonValidator()->validateFeedback($email_id, $object_id)) {
                    $this->addFlash('success', $this->phrase('portal.flashes.validated_email'));
                } else {
                    $this->addFlash('error', $this->phrase('portal.flashes.validated_email'));
                }
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

        return $this->redirectToRoute('portal_home');
    }

    /**
     * @Route("/validate-send/{object_type}/{email_id}/{object_id}", name="portal_send_validation", defaults={"object_id":null})
     */
    public function resendValidationEmailAction(Request $request, $object_type, $email_id, $object_id)
    {
        switch ($object_type) {
            case PersonValidator::TYPE_EMAIL:
                $this->getPersonValidator()->doResendLink(PersonValidator::TYPE_EMAIL, $email_id, null, true);
                $this->addFlash('success', $this->phrase('portal.flashes.sent_verification_email_secondary'));
                break;
            case PersonValidator::TYPE_EMAIL_PRIMARY:
                $this->getPersonValidator()->doResendLink(PersonValidator::TYPE_EMAIL_PRIMARY, $email_id);
                $this->addFlash('success', $this->phrase('portal.flashes.sent_verification_email_primary'));
                break;
            case PersonValidator::TYPE_FEEDBACK:
                $this->getPersonValidator()->doResendLink(PersonValidator::TYPE_FEEDBACK, $email_id, $object_id);
                $this->addFlash('success', $this->phrase('portal.flashes.new_feedback_verify'));
                break;
        }

        return $this->redirectToRoute('portal_home');
    }

    /**
     * @Route("/change-language", name="portal_change_language")
     */
    public function changeLanguageAction(Request $request)
    {
        $new_lang_code = $request->get('lang_code');
        $referer       = $request->server->get('HTTP_REFERER');

        $lang_changer = $this->get('language_changer');
        $redirect_url = $lang_changer->changeLanguage($new_lang_code, $referer);

        $person = $this->getCurrentPerson();
        if (!$person instanceof PersonGuest) {
            if ($lang = $this->get('language_manager')->getLanguage($new_lang_code)) {
                $person->setLanguage($lang);
                $this->persistAndFlushEntity($person);
            }
        }

        return $this->redirect($redirect_url);
    }

    /**
     * @Route("/dismiss-lang-alert", name="portal_dismiss_lang_alert")
     */
    public function ignoreLangAlert(Request $request)
    {
        $this->getSession()->set('ignore_language_warning', true);

        $referer = $request->server->get('HTTP_REFERER');

        return $this->redirect($referer);
    }

    /**
     * This is used by JS forms that need to periodically check if they need to render a CAPTCHA
     * and if so, what HTML they should use to render it.
     *
     * @Route("/captcha-html", name="portal_captcha_html")
     */
    public function catpchaHtmlAction(Request $request)
    {
        $action = $request->get('action');

        // if we know its for a login form, we can return a blank response if it's unnecessary
        if ($action === 'login') {
            $check = new LoginAbuseCheck(null, $request->getClientIp());
            $check->markAsCheckOnly();
            $this->getAntiAbuseService()->check($check);
            if (!$check->isCaptchaRecommended()) {
                return new JsonResponse([
                    'captcha_required' => false,
                ]);
            }
        }

        //$form = $this->createFormBuilder()->add('captcha', 'deskpro_captcha')->getForm();

        return new JsonResponse(
            [
                'captcha_required' => true,
            ]
        );
    }

    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo   = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Person\PersonValidator
     */
    protected function getPersonValidator()
    {
        return $this->get('person.portal_validator');
    }
}
