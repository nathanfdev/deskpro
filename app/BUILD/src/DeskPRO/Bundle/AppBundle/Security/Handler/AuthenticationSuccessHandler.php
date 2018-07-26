<?php

namespace DeskPRO\Bundle\AppBundle\Security\Handler;

use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use Orb\Auth\Adapter\SsoLoginActionInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Http\ParameterBagUtils;

/**
 * When a login succeeds, this class does logging, checks, etc and then redirects the user to the right url.
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler implements ContainerAwareInterface, LogoutSuccessHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($token instanceof AgentImpersonateToken) {
            $request->getSession()->set('auth_person_id', $token->getAgent()->getId());

            if ($request->isXmlHttpRequest()) {
                return $this->getResponseForAjax('/');
            }

            return $this->httpUtils->createRedirectResponse($request, '/');
        }

        // do this after the AgentImpersonateToken bit above
        // this allows agents/admin to login to portal and seamlessly move to other interfaces
        $request->getSession()->set('auth_person_id', $token->getUser()->getId());

        if (
            $token->hasAttribute(SsoLoginActionInterface::TOKEN_ATTRIBUTE_BACKGROUND_REFRESH)
            && $token->getAttribute(SsoLoginActionInterface::TOKEN_ATTRIBUTE_BACKGROUND_REFRESH)
        ) {
            $token->setAttribute(SsoLoginActionInterface::TOKEN_ATTRIBUTE_BACKGROUND_REFRESH, false);

            return $this->container->get('templating')->renderResponse('DeskPRO:Auth:_sso_refresh.html.twig');
        }

        // if we should be auto submitting, send to auto submit controller (this was a login intercept)
        if ($this->container->get('form_saver')->getAutoSubmitSavedForm()) {
            $savedFormUrl = $this->container->get('router')->generate('saved_form_auto_submit');

            if ($request->isXmlHttpRequest()) {
                return $this->getResponseForAjax($savedFormUrl);
            }

            return $this->httpUtils->createRedirectResponse(
                $request,
                $savedFormUrl
            );
        }

        try {
            $userLanguage = $token->getUser()->getLanguage();
            if (!$userLanguage) {
                $userLanguage = $this->container->get('language_stack')->getActive();
            }

            $this->container->get('language_stack')->push($userLanguage);
            $redirectUrl = $this->determineTargetUrl($request);
        } finally {
            $this->container->get('language_stack')->pop();
        }

        // Never redirect back to login controller, can cause loops
        if (strpos($redirectUrl, 'login') !== false) {
            $redirectUrl = $this->options['default_target_path'];
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getResponseForAjax($redirectUrl);
        }

        return $this->httpUtils->createRedirectResponse($request, $redirectUrl);
    }

    protected function getResponseForAjax($redirect)
    {
        return new JsonResponse(
            [
                'success'  => true,
                'redirect' => $redirect,
            ]
        );
    }

    /**
     * Builds the target URL according to the defined options.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function determineTargetUrl(Request $request)
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }

        // the login url can't be the login destination
        $login_url = $this->container->get('router')->generate('portal_login');

        if ($targetUrl = $request->get($this->options['target_path_parameter'], null, true)) {
            if ($targetUrl != $login_url) {
                return $targetUrl;
            }
        }

        if ($targetUrl = ParameterBagUtils::getRequestParameterValue($request, $this->options['target_path_parameter'])) {
            return $targetUrl;
        }

        if (null !== $this->providerKey && $targetUrl = $request->getSession()->get(
                '_security.'.$this->providerKey.'.target_path'
            )
        ) {
            $request->getSession()->remove('_security.'.$this->providerKey.'.target_path');

            if ($targetUrl != $login_url && strpos($targetUrl, '_proxy') === false) {
                try {
                    $targetRequest = Request::create($targetUrl);
                    $targetInfo    = $this->container->get('router')->matchRequest($targetRequest);

                    return $this->container->get('router')->generate($targetInfo['_route'], $targetRequest->query->all());
                } catch (\Exception $e) {
                    // unable to parse target url, redirect to it as is
                }

                return $targetUrl;
            }
        }

        if ($this->options['use_referer'] && ($targetUrl = $request->headers->get('Referer'))) {
            if (
                $targetUrl !== $login_url
                && $targetUrl !== $this->httpUtils->generateUri($request, $this->options['login_path'])
                && $this->isValidRedirectUrl($targetUrl, $request)
            ) {
                return $targetUrl;
            }
        }

        return $this->container->get('router')->buildUrl($this->options['default_target_path']);
    }

    /**
     * Verify that the target redirect (e.g. from referer) is a local redirect and not
     * offsite. This is important to prevent loops (eg. redirect back to an sso site).
     *
     * @param string  $url
     * @param Request $request
     *
     * @return bool
     */
    private function isValidRedirectUrl($url, Request $request)
    {
        $hostChecker = $this->container->get('url_host_checker');

        // matches curent request, this is ok
        if ($hostChecker->isMatch(
            $url,
            $request->getHost(),
            $request->getPort()
        )) {
            return true;
        }

        $brandStack  = $this->container->get('brand_stack');
        $activeBrand = $brandStack->getActive();
        $brandUrl    = $activeBrand->getSetting('core.deskpro_url');

        // matches brand url
        if ($brandUrl && $hostChecker->isMatchUrl($url, $brandUrl)) {
            return true;
        }

        return false;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function onLogoutSuccess(Request $request)
    {
    }
}
