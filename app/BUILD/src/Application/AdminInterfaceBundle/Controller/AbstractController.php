<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Service\CheckWhitelistedIP;
use Symfony\Component\HttpFoundation\Request;

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
    }

    /**
     * Check if the global request token check is required for the request.
     */
    public function requireRequestToken($action, $arguments = null)
    {
        // Pre install we dont have a secret yet
        // So dont require the request token on POSTs
        // while we fill out setup form
        if (!App::getSetting('core.setup_initial')) {
            return false;
        }

        if ($this->request->getMethod() == 'POST') {
            return true;
        }

        return false;
    }

    /**
     * Force a login.
     *
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        $return = '';
        if (!$this->person['id']) {
            if ($request->getMethod() === 'POST') {
                $return = $this->get('router')->generate('admin');
            } else {
                $return = $request->getRequestUri();
            }
        }

        if (!$this->_userHasPermissions()) {
            if ($request->isXmlHttpRequest()) {
                $data = ['error' => 'session_expired'];

                return $this->createJsonResponse($data, 403);
            }

            return $this->render('AgentBundle:Login:redirect-login.html.twig', [
                'return' => $return,
            ]);
        }

        if ($this->requireRequestToken($action, $arguments) && !$this->checkRequestToken('request_token', '_rt')) {
            if ($request->isXmlHttpRequest()) {
                $data = [
                    'error'          => 'invalid_request_token',
                    'redirect_login' => $this->generateUrl('agent_login'),
                ];

                return $this->createJsonResponse($data, 403);
            } else {
                return $this->standardErrorResponse('The form you are trying to submit has expired. Please go back and try again.');
            }
        }

        if (!CheckWhitelistedIP::checkIP($request, $this->container, $this->person)) {
            return $this->render('AgentBundle:Login:whitelist-ip.html.twig', [
                'ip' => $this->getRequest()->getClientIp(),
            ]);
        }

        return;
    }

    /**
     * @param string $error_message
     * @param string $error_title
     * @param int    $code
     * @param array  $vars
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function standardErrorResponse($error_message = '', $error_title = '', $code = 200, array $vars = [])
    {
        $tpl_standard = 'UserBundle:Main:error-standard.html.twig';
        $tpl_specific = "UserBundle:Main:error-{$code}.html.twig";

        $tpl = $tpl_standard;
        if (App::getTemplating()->exists($tpl_specific)) {
            $tpl = $tpl_specific;
        }

        $vars = array_merge(
            $vars, [
                'error_message' => $error_message,
                'error_title'   => $error_title,
            ]
        );

        $res = $this->render($tpl, $vars);

        $res->setStatusCode($code);

        return $res;
    }

    protected function _userHasPermissions()
    {
        if ($this->person->is_agent && $this->person->can_admin) {
            return true;
        }

        return false;
    }
}
