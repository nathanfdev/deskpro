<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\JIRA\OAuthWrapper;
use Application\DeskPRO\Service\JIRA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JiraController extends AbstractController
{
    /**
     * todo.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function tokenAction(Request $request)
    {
        $oauth = new OAuthWrapper(
            $this->get(JIRA::NAME),
            $this->generateUrl('jira_token', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $verifier    = $request->get('oauth_verifier');
        $credentials = $request->getSession()->get('jira_oauth');

        if ($back = $request->get('back_url')) {
            $request->getSession()->set('jira_back_url', $back);
        }

        if ($verifier && $credentials) {
            $oauth->requestAuthCredentials(
                $credentials['oauth_token'],
                $credentials['oauth_token_secret'],
                $verifier
            );
            $request->getSession()->remove('jira_oauth');

            if ($back = $request->getSession()->get('jira_back_url')) {
                $request->getSession()->remove('jira_back_url');
            }

            return $this->redirect($back ?: $this->generateUrl('admin'));
        }

        $credentials = $oauth->requestTempCredentials();
        $request->getSession()->set('jira_oauth', $credentials);

        return $this->redirect($oauth->getAuthUrl());
    }
}
