<?php

/**
 * DeskPRO.
 */

namespace Application\ReportsInterfaceBundle\Controller;

use Application\DeskPRO\Service\CheckWhitelistedIP;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        if (!$this->_userHasPermissions()) {
            if ($request->isXmlHttpRequest()) {
                $data = ['error' => 'session_expired'];

                return $this->createJsonResponse($data, 403);
            }

            return $this->redirectRoute('agent');
        }

        if ($this->requireRequestToken($action, $arguments) && !$this->checkRequestToken('request_token', '_rt')) {
            if ($request->isXmlHttpRequest()) {
                $data = [
                    'error'          => 'invalid_request_token',
                    'redirect_login' => $this->generateUrl('agent_login'),
                ];

                return $this->createJsonResponse($data, 403);
            } else {
                throw new BadRequestHttpException('The form you are trying to submit has expired. Please go back and try again.');
            }
        }

        if (!CheckWhitelistedIP::checkIP($this->getRequest(), $this->container, $this->person)) {
            return $this->render('AgentBundle:Login:whitelist-ip.html.twig', [
                'ip' => $this->getRequest()->getClientIp(),
            ]);
        }

        return;
    }

    protected function _userHasPermissions()
    {
        if ($this->person->is_agent && $this->person->can_reports) {
            return true;
        }

        return false;
    }
}
