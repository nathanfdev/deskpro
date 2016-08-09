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

/**
 * DeskPRO.
 */

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
            $saved_form_url = $this->container->get('router')->generate('saved_form_auto_submit');

            if ($request->isXmlHttpRequest()) {
                return $this->getResponseForAjax($saved_form_url);
            }

            return $this->httpUtils->createRedirectResponse(
                $request,
                $saved_form_url
            );
        }

        $redirect_url = $this->determineTargetUrl($request);

        // Never redirect back to login controller, can cause loops
        if (strpos($redirect_url, 'login') !== false) {
            $redirect_url = $this->options['default_target_path'];
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getResponseForAjax($redirect_url);
        }

        return $this->httpUtils->createRedirectResponse($request, $redirect_url);
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
                return $targetUrl;
            }
        }

        if ($this->options['use_referer'] && ($targetUrl = $request->headers->get(
                'Referer'
            )) && $targetUrl !== $this->httpUtils->generateUri($request, $this->options['login_path'])
        ) {
            if ($targetUrl != $login_url) {
                return $targetUrl;
            }
        }

        return $this->options['default_target_path'];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function onLogoutSuccess(Request $request)
    {
    }
}
