<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\DependencyInjection\SystemServices\UsersourceAuthAdapterFactoryService;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Template;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\UploadAbuseCheck;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Security\DpTransferSessionAuthToken;
use DeskPRO\Bundle\PortalBundle\Form\Form\Extension\CsrfDoubleSubmitExtension;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Component\Util\RandUtils;
use Orb\Auth\Adapter\SamlAdapterInterface;
use Orb\Util\Util;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class PortalController.
 */
class PortalController extends AbstractController
{
    /**
     * @Route("/template_editor", name="portal_temp")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function tempAction(Request $request)
    {
        $t_repo        = $this->getRepo(Template::class);
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
    public function homeAction()
    {
        $allowedFeedbackTypes = $this->getPermissionBagForCurrentUser()->getAllowedFeedbackCategoryIds();
        if (!$this->getUser() && $this->canUseNothing()) {
            return $this->redirectToRoute('portal_login');
        }

        if ($redirectToApp = $this->getOneAppRedirect()) {
            return $redirectToApp;
        }

        return $this->renderThemeView('Theme:Portal:home.html.twig',
            [
                'page_title'    => $this->createPageTitle()->homepage(),
                'feedbackTypes' => $allowedFeedbackTypes,
            ]
        );
    }

    /**
     * @Route("/login", name="portal_login")
     * @Route("/login", name="user_login")
     *
     * @PageHttpCache()
     *
     * @param Request $request
     *
     * @return Response
     */
    public function loginAction(Request $request)
    {
        if ($this->getUser() instanceof Person && !$this->getUser() instanceof PersonGuest) {
            return $this->redirectToRoute('portal_user_profile');
        }

        if (($token = $request->query->get('tok')) && strpos($token, '-')) {
            list($person_id, $login_token) = explode('-', $token, 2);

            /** @var Person $person */
            $person = $this->getEm()->find(Person::class, $person_id);

            if ($person && $person->checkPassword($login_token)) {
                $token = new DpTransferSessionAuthToken($person, 'token_login_'.RandUtils::randomString(20));
                $this->get('security.token_storage')->setToken($token);

                if ($request->query->get('return')) {
                    return $this->redirect($request->query->get('return'));
                }

                return $this->redirectToRoute('portal_home');
            }
        }

        $saved_form_type = null;
        if ($saved_form = $this->getFormSaver()->getByExternalCode($request->get('saved_form'))) {
            // this person just filled out a form and is being asked to login to auto-submit it
            $saved_form_type = $this->getFormSaver()->getDataType($saved_form);
        }

        $captcha_form  = null;
        $last_username = $request->hasPreviousSession() ? $this->getSession()->get('last_username') : null;
        $abuse_check   = new LoginAbuseCheck($last_username, $request->getClientIp());
        $abuse_check->markAsCheckOnly();
        $this->getAntiAbuseService()->check($abuse_check);
        if ($abuse_check->isCaptchaRecommended()) {
            $captcha_form = $this->createForm(DpCaptchaType::class);
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

        if ($error = $request->getSession()->get(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $error instanceof AuthenticationException
                ? $error->getMessage()
                : 'portal.account.login-invalid';
        }

        return $this->renderThemeView(
            'Theme:Portal:User/login.html.twig',
            [
                'auth_manager'         => $this->get('dp_authentication_manager.user'),
                'login_captcha_failed' => $request->get('retry') == 'captcha',
                'login_error'          => $error,
                'lockout_error'        => $abuse_check->isLockoutRecommended(),
                'lockout_time'         => $abuse_check->getLockoutTime(true),
                'saved_form'           => $saved_form,
                'saved_form_type'      => $saved_form_type,
                'captcha_form'         => $captcha_form ? $captcha_form->createView() : null,
                'last_username'        => $last_username,
                'reset_success'        => $request->get('reset_success', 0),
                'set_password_success' => $request->get('set_password_success', 0),
                'breadcrumbs'          => $this->getBreadcrumbGenerator()->buildLogin(),
                'page_title'           => $this->createPageTitle()->loginPage(),
                'usersources_view'     => $usersources_view,
                'destination'          => $destination,
            ]
        );
    }

    /**
     * @Route("/login/magic_link/{authId}", name="portal_magic_link_login")
     *
     * @param string $authId
     *
     * @return Response
     */
    public function loginMagicLinkController($authId)
    {
        $tmpData = $this->getRepo(TmpData::class)->findOneBy([
            'auth' => $authId,
        ]);

        if (!$tmpData instanceof TmpData || !$tmpData->getData('email')) {
            return $this->renderThemeView('Theme:Error:error_custom.html.twig', [
                'error_title' => 'portal.account.link-expired',
            ]);
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
        $personRepo = $this->getRepo(Person::class);
        $person     = $personRepo->findOneByEmail($tmpData->getData('email'));

        if (!$person instanceof Person) {
            return $this->renderThemeView('Theme:Error:error_custom.html.twig', [
                'error_title' => 'portal.account.link-expired',
            ]);
        }

        // create api token
        $token = new ApiToken();
        $token->setPerson($person);
        $token->setScope(ApiToken::SCOPE_CLIENT);

        $this->getEm()->persist($token);
        $this->getEm()->flush();

        // remove temp auth code
        $this->getEm()->remove($tmpData);
        $this->getEm()->flush();

        $res = $this->render('ApiBundle::ApiTokens/custom_target.html.twig', [
            'target_url' => $tmpData->getData('target'),
            'token'      => $token,
        ]);

        $res->headers->set('Content-Type', 'text/html');

        return $res;
    }

    /**
     * @Route("/logout/{auth}", name="user_logout")
     *
     * @param string $auth
     *
     * @return RedirectResponse
     */
    public function legacyLogoutLinkAction($auth, Request $request)
    {
        $appSecret = $this->get('settings_resolver')->getGlobalSettings()->get('core.app_secret', '');

        if (!Util::checkStaticSecurityToken($auth, md5($appSecret.'user_logout'))) {
            throw $this->createNotFoundException();
        }

        $url = $this->get('security.logout_url_generator')->getLogoutUrl('portal');
        if ($request->get('to')) {
            $url .= '&to='.$request->get('to');
        }

        return new RedirectResponse($url);
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
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function changeLanguageAction(Request $request)
    {
        $newLangCode = $request->get('lang_code');
        $referer     = $request->server->get('HTTP_REFERER');
        $langChanger = $this->get('language_changer');

        try {
            $redirectUrl = $langChanger->changeLanguage($newLangCode, $referer);
        } catch (\Exception $e) {
            $redirectUrl = $this->get('router')->generate('portal_home');
        }

        $person = $this->getCurrentPerson();
        if (!$person instanceof PersonGuest) {
            if ($lang = $this->get('language_manager')->getLanguage($newLangCode)) {
                $person->setLanguage($lang);
                $this->persistAndFlushEntity($person);
            }
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/dismiss-lang-alert", name="portal_dismiss_lang_alert")
     *
     * @param Request $request
     *
     * @return RedirectResponse
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
     *
     * @param Request $request
     *
     * @return JsonResponse
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

        return new JsonResponse([
            'captcha_required' => true,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
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
     * @Route("/dpblob/{restrictionSet}", name="portal_custom_blob_upload")
     * @Route("/dpblob", name="portal_blob_upload")
     * @Method("POST")
     *
     * @param Request $request
     * @param string  $restrictionSet
     *
     * @return JsonResponse
     */
    public function uploadBlobAction(Request $request, $restrictionSet = null)
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

        $error = $this->get('attachment_accepter')->getError($file, $restrictionSet ? $restrictionSet.'.user' : 'user');
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

        $props = [];
        if ($request->query->get('tag', '')) {
            switch (trim($request->query->get('tag', ''))) {
                case 'ticket_attachment':
                    $props['tag'] = 'ticket_attachment';
                    break;
            }
        }

        /** @var Blob $blob */
        $blob = $this->get('attachment_accepter')->accept($file, true, $props);

        return new JsonResponse([
            'success' => true,
            'blob'    => [
                'id'        => $blob->getId(),
                'filename'  => $blob->getFilename(),
                'authcode'  => $blob->getAuthcode(),
                'size'      => $blob->getReadableFilesize(),
                'icon_html' => $this->get('icon_factory')->makeFileIcon($blob),
                'is_image'  => $blob->isImage(),
                'url'       => $blob->getDownloadUrl(true),
            ],
        ]);
    }

    /**
     * @Route("/saml/metadata/{usersource_id}.xml", name="portal_saml_metadata")
     * @Route("/saml/metadata/{usersource_id}.xml", name="user_saml_metadata")
     *
     * @param int $usersource_id
     *
     * @return Response
     */
    public function samlMetadataAction($usersource_id)
    {
        /** @var Usersource $usersource */
        $usersource = $this->getEm()->find(Usersource::class, $usersource_id);
        if (!$usersource) {
            throw $this->createNotFoundException();
        }

        /** @var DeskproContainer $container */
        $container = $this->container;
        $type      = $usersource->getType() ?: null;
        if ($type) {
            /** @var UsersourceAuthAdapterFactory $factory */
            $factory = UsersourceAuthAdapterFactoryService::create($container, ['interface' => $type]);
        } else {
            /** @var UsersourceAuthAdapterFactory $factory */
            $factory = $container->getSystemService('usersource_auth_adapter_factory');
        }
        $adapter = $factory->getAuthAdapter($usersource, null, $type);
        if ($adapter instanceof SamlAdapterInterface) {
            return $adapter->getMetadataXmlResponse();
        }

        throw $this->createNotFoundException('usersource / adapter not suitable for SLS');
    }

    /**
     * @Route("/.well-known/apple-app-site-association", name="portal_apple_app_site_assoc")
     *
     * @return JsonResponse
     */
    public function appleAppSiteAssociationAction()
    {
        $router = $this->get('router');
        $paths  = [
            $router->generate('go_to_ticket_id', ['id' => 0]),
            $router->generate('go_to_person_id', ['id' => 0]),
            $router->generate('go_to_organization_id', ['id' => 0]),
        ];

        foreach ($paths as &$path) {
            $path = str_replace(0, '*', $path);
        }

        $data = [
            'applinks' => [
                'apps'    => [],
                'details' => [
                    [
                        'appID' => 'HC9N5Z797X.com.deskpro.mobile.ios',
                        'paths' => $paths,
                    ],
                ],
            ],
        ];

        $response = new JsonResponse();
        $response->setEncodingOptions(\JSON_UNESCAPED_SLASHES);
        $response->setData($data);

        return $response;
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
     *
     * @param Request $request
     * @param Blob    $blob
     *
     * @return JsonResponse
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
        try {
            $this->getAntiAbuseService()->check($check);
        } finally {
            if ($check->isLimited()) {
                return new JsonResponse([
                    'success' => false,
                    'error'   => [
                        'code' => 'rate_limit',
                    ],
                ]);
            }
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

        return;
    }

    /**
     * @return bool
     */
    private function canUseNothing()
    {
        return $this->container->get('navigation_helper')->hasNoActiveApps();
    }

    /**
     * @return RedirectResponse
     */
    private function getOneAppRedirect()
    {
        $navigationHelper = $this->container->get('navigation_helper');
        if ($route = $navigationHelper->getRedirectRouteForOneApp()) {
            return $this->redirectToRoute($route);
        }

        return false;
    }
}
