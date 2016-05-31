<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Template;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\UploadAbuseCheck;
use DeskPRO\Bundle\AppBundle\Security\DpTransferSessionAuthToken;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\CsrfDoubleSubmitExtension;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Component\Util\RandUtils;
use Orb\Auth\Adapter\SamlAdapterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PortalController extends AbstractController
{
    /**
     * @Route("/template_editor", name="portal_temp")
     */
    public function tempAction(Request $request)
    {
        $t_repo        = $this->getRepo('DeskPRO:Template');
        $themeset      = $this->getPortalBrandTheme()->getActiveThemeSet();
        $template_name = $request->get('template_name');

        if ($request->getMethod() === 'POST') {
            $template_name = $request->get('template_name');
            $code          = $request->get('template_code');

            if (!$tem = $t_repo->findOneBy(['theme_set' => $themeset, 'name' => $template_name])) {
                $tem               = new Template();
                $tem->name         = $template_name;
                $tem->date_created = new \DateTime();
            }

            $tem->theme_set     = $themeset;
            $tem->template_code = $code;
            $tem->date_updated  = new \DateTime();

            $tem->template_compiled = $this->get('twig')->compileSource($code, $template_name);

            $this->persistAndFlushEntity($tem);

            $this->addFlash('success', 'saved');

            $this->redirectToRoute('portal_temp', ['template_name' => $template_name]);
        }

        $tem = null;
        if ($template_name) {
            $tem = $t_repo->findOneBy(['theme_set' => $themeset, 'name' => $template_name]);
        }

        $theme = $this->getPortalBrandTheme()->getActiveTheme();

        return $this->renderThemeView('Theme:Temp:customTemplate.html.twig', [
            'template_map' => $theme->getTemplateMap(),
            'template'     => $tem,
        ]);
    }

    /**
     * @Route("/", name="portal_home")
     * @Route("/", name="user")
     * @PageHttpCache()
     */
    public function homeAction(Request $request)
    {
        $allowedFeedbackTypes = $this->getPermissionBagForCurrentUser()->getAllowedFeedbackCategoryIds();

        return $this->renderThemeView('Theme:Portal:home.html.twig',
            array(
                'page_title'    => $this->createPageTitle()->homepage(),
                'feedbackTypes' => $allowedFeedbackTypes,
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

        if (($token = $request->query->get('tok')) && strpos($token, '-')) {
            list($person_id, $login_token) = explode('-', $token, 2);

            /** @var \Application\DeskPRO\Entity\Person $person */
            $person = $this->getEm()->find('DeskPRO:Person', $person_id);

            if ($person && $person->checkPassword($login_token)) {
                $token = new DpTransferSessionAuthToken($person, 'token_login_'.RandUtils::randomString(20));
                $this->get('security.token_storage')->setToken($token);

                return $this->redirectToRoute('portal_home');
            }
        }

        $saved_form_message = null;
        if ($saved_form = $this->getFormSaver()->getByExternalCode($request->get('saved_form'))) {
            // this person just filled out a form and is being asked to login to auto-submit it
            $saved_form_message = $this->getFormSaver()->getMessage($saved_form);
        }

        $captcha_form  = null;
        $last_username = $request->hasPreviousSession() ? $this->getSession()->get('last_username') : null;
        $abuse_check   = new LoginAbuseCheck($last_username, $request->getClientIp());
        $abuse_check->markAsCheckOnly();
        $this->getAntiAbuseService()->check($abuse_check);
        if ($abuse_check->isCaptchaRecommended()) {
            $captcha_form = $this->createForm('deskpro_captcha');
        }
        $usersources_view = $this->get('usersources_view_helper')->createUsersourceViewList();

        $destination = $request->query->get('_destination', false);
        if (!$destination || !is_string($destination)) {
            if ($destination = $request->server->get('HTTP_REFERER')) {
                if (false !== stripos($destination, '/login')) {
                    $destination = null;
                }
            } else {
                $destination = null;
            }
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
                'usersources_view'     => $usersources_view,
                'destination'          => $destination,
            )
        );
    }

    /**
     * @Route("/logout/{auth}", name="user_logout")
     *
     * @return RedirectResponse
     */
    public function legacyLogoutLinkAction($auth)
    {
        $appSecret = $this->get('settings_resolver')->getGlobalSettings()->get('core.app_secret', '');

        if (!\Orb\Util\Util::checkStaticSecurityToken($auth, md5($appSecret.'user_logout'))) {
            throw $this->createNotFoundException();
        }

        return new RedirectResponse($this->get('security.logout_url_generator')->getLogoutUrl('portal'));
    }

    /**
     * Display logout button.
     *
     * This action is used to render logout confirmation button in case of LogoutException because of invalid CSRF.
     *
     * @Route("/logout-confirmation", name="portal_logout_confirm")
     */
    public function confirmLogoutAction()
    {
        return $this->renderThemeView(
            'PortalBundle:Logout:confirmation.html.twig',
            ['url' => $this->get('security.logout_url_generator')->getLogoutUrl('portal')]
        );
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
    public function ignoreLangAlertAction(Request $request)
    {
        $this->getSession()->set('ignore_language_warning', true);

        $referer = $request->server->get('HTTP_REFERER');
        if (!$referer) {
            return $this->redirectToRoute('portal_home');
        }

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
     * SEND a POST request with a file called "file[blob]" and a CSRF called "file[_dp_csrf_token]".
     *
     * If the JSON response has "success" === true, then you can use "blob.id" and "blob.authcode"
     *
     * You will be checked for rate limit settings... see: 'rate_limit.upload_attachment.guest.limit'
     * and 'rate_limit.upload_attachment.limit'
     *
     * You will be checked for CSRF. Make sure the val pf a cookie named "_dp_csrf_token" is the same
     * and as the csrf posted with "file[_dp_csrf_token]"
     *
     *
     * @Route("/dpblob", name="portal_blob_upload")
     * @Method("POST")
     */
    public function uploadBlobAction(Request $request)
    {
        $response = $this->checkRateLimitAndCsrf($request);
        if ($response) {
            return $response;
        }

        $file = $request->files->get('file[blob]', null, true);
        if (!$file instanceof UploadedFile) {
            return new JsonResponse([
                'success' => false,
                'error'   => [
                    'code' => 'no_file_in_request',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $error = $this->get('attachment_accepter')->getError($file, 'user');
        if ($error) {
            $error_code = $error['error_code'];
            $params     = [];

            $error_detail = $error['error_detail'];
            if ($error_detail) {
                $params = ['detail' => $error_detail];
            }

            $phrase = sprintf('portal.forms.error_accept_%s', $error_code);

            return new JsonResponse([
                'success' => false,
                'error'   => [
                    'message' => $this->phrase($phrase, $params),
                    'code'    => $error_code,
                    'detail'  => $error_detail,
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $blob = $this->get('attachment_accepter')->accept($file, true);

        return new JsonResponse([
            'success' => true,
            'blob'    => [
                'id'        => $blob->getId(),
                'filename'  => $blob->getFilename(),
                'authcode'  => $blob->getAuthcode(),
                'size'      => $blob->getReadableFilesize(),
                'icon_html' => $this->get('icon_factory')->makeFileIcon($blob),
                'is_image'  => $blob->isImage(),
                'url'       => $this->generateUrl(
                    'serve_blob',
                    [
                        'blob_auth_id' => $blob->getAuthcode(),
                        'filename'     => $blob->getFilenameSafe(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
        ]);
    }

    /**
     * @Route("/saml/metadata/{usersource_id}.xml", name="portal_saml_metadata")
     * @Route("/saml/metadata/{usersource_id}.xml", name="user_saml_metadata")
     */
    public function samlMetadataAction($usersource_id)
    {
        /** @var \Application\DeskPRO\Entity\Usersource $usersource */
        $usersource = $this->getEm()->find('DeskPRO:Usersource', $usersource_id);
        if (!$usersource) {
            throw $this->createNotFoundException();
        }
        /** @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $factory */
        $factory = $this->container->getSystemService('usersource_auth_adapter_factory');
        $adapter = $factory->getAuthAdapter($usersource);

        if ($adapter instanceof SamlAdapterInterface) {
            return $adapter->getMetadataXmlResponse();
        }

        throw $this->createNotFoundException('usersource / adapter not suitable for SLS');
    }

    /**
     * If you know the primary ID and the authcode of blob, you can delete that blob.
     *
     * SEND a DELETE request with a CSRF called "file[_dp_csrf_token]" (with a cookie with the
     * name "_dp_csrf_token" of the same value).
     *
     * You will get a 404 if the blob isn't found.
     * You will get a "success" === false response if you fail CSRF and/or rate limit.
     * You will get a "success" === false if the delete failed.
     * And a "success" === true if the delete succeeded.
     *
     * You will be checked for rate limit settings... see: 'rate_limit.upload_attachment.guest.limit'
     * and 'rate_limit.upload_attachment.limit'
     *
     * You will be checked for CSRF. Make sure the val pf a cookie named "_dp_csrf_token" is the same
     * and as the csrf posted with "file[_dp_csrf_token]"
     *
     * @Route("/dpblob/{id}-{authcode}", name="portal_blob_delete")
     * @Method("DELETE")
     */
    public function deleteBlobAction(Request $request, Blob $blob)
    {
        if ($response = $this->checkRateLimitAndCsrf($request)) {
            return $response;
        }

        // assuming the authcode is all that is necessary to delete a blob here
        $success = $this->get('blob.storage')->deleteBlobRecord($blob);

        return new JsonResponse(['success' => (bool) $success]);
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Person\PersonValidator
     */
    protected function getPersonValidator()
    {
        return $this->get('person.portal_validator');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|null
     */
    private function checkRateLimitAndCsrf(Request $request)
    {
        // Skip check for portal api
        if (strpos($request->getPathInfo(), '/portal/api') === 0) {
            return;
        }

        // rate limit first
        $check = new UploadAbuseCheck($this->getUser(), $request->getClientIp());
        $this->getAntiAbuseService()->check($check);
        if ($check->isCaptchaRecommended()) {
            return new JsonResponse([
                'success' => false,
                'error'   => [
                    'code' => 'rate_limit',
                ],
            ]);
        }

        $cookie_val = (string) $request->cookies->get(CsrfDoubleSubmitExtension::COOKIE_NAME);
        $submit_val = (string) $request->request->get('file['.CsrfDoubleSubmitExtension::COOKIE_NAME.']', null, true);

        if (!(strlen($cookie_val) >= 5 && $cookie_val === $submit_val)) {
            return new JsonResponse([
                'success' => false,
                'error'   => [
                    'code' => 'csrf',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
