<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Service\CheckWhitelistedIP;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    public $person;

    protected function init()
    {
        parent::init();

        $this->person = $this->session->getPerson();

        if (!$this->person->id) {
            $cas = new \Application\AgentBundle\Controller\Helper\CarryAdminSession($this);
            $cas->process();
        }
    }

    /**
     * Check if the global request token check is required for the request.
     */
    protected function requireRequestToken($action, $arguments = null)
    {
        return true;
    }

    /**
     * Force a login.
     *
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        if (!$this->person['id']) {
            if ($request->isXmlHttpRequest()) {
                $data = [
                    'error'          => 'session_expired',
                    'redirect_login' => $this->generateUrl('agent_login'),
                ];

                return $this->createJsonResponse($data, 403);
            } else {
                if ($this->getRequest()->getMethod() === 'POST') {
                    $return = $this->get('router')->generate('agent');
                } else {
                    $return = $request->getRequestUri();
                }

                return $this->render('AgentBundle:Login:redirect-login.html.twig', [
                    'return'      => $return,
                    'disable_sso' => $request->get('disable_sso'),
                ]);
            }
        }

        if (!$this->_userHasPermissions()) {
            return $this->redirectRoute('user');
        }

        if ($this->requireRequestToken($action, $arguments) && !$this->checkRequestToken('request_token', '_rt')) {
            if ($request->isXmlHttpRequest()) {
                $data = [
                    'error'          => 'invalid_request_token',
                    'redirect_login' => $this->generateUrl('agent_login'),
                ];

                return $this->createJsonResponse($data, 403);
            } else {
                return $this->render('AgentBundle:Login:redirect-login.html.twig', [
                    'return'      => $this->get('router')->generate('agent'),
                    'disable_sso' => $request->get('disable_sso'),
                ]);
            }
        }

        if (!CheckWhitelistedIP::checkIP($request, $this->container, $this->person)) {
            return $this->render('AgentBundle:Login:whitelist-ip.html.twig', [
                'ip' => $request->getClientIp(),
            ]);
        }

        $this->person->loadHelper('Agent');
        $this->person->loadHelper('AgentTeam');
        $this->person->loadHelper('AgentPermissions');
        $this->person->loadHelper('PermissionsManager', ['force_load_usergroups' => true]);
        $this->person->loadHelper('HelpMessages');
        $this->person->loadHelper('AgentPrefs');
    }

    protected function _userHasPermissions()
    {
        if ($this->person->is_agent && $this->person->can_agent) {
            return true;
        }

        return false;
    }

    /**
     * Create a response that indicates a permissions error.
     *
     * @param string $message The message to show the user
     *
     * @return Response
     */
    protected function createPermissionErrorResponse($message)
    {
        return $this->createJsonResponse(['error' => 'not_allowed', 'message' => $message], 403);
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}
