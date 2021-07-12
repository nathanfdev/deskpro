<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\Service\CheckWhitelistedIP;
use Application\DeskPRO\Translate\Translate;
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
     *
     * @param mixed $action
     * @param null|mixed $arguments
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

        $request = $this->get('request_stack')->getMasterRequest() ?: $request;
        if (!CheckWhitelistedIP::checkIP($request, $this->container, $this->person, $this)) {
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
        $this->container->getTranslator()->setPersonContext($this->person);
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

    /**
     * @param array $fieldErrors
     * @param array $postCustomFields
     */
    protected function validateCustomFields(&$fieldErrors, $postCustomFields, FieldManager $fieldManager)
    {
        /** @var Translate $trans */
        $trans        = $this->container->getTranslator();
        $customFields = $fieldManager->getDefinedFields();

        foreach ($customFields as $field) {
            $errors = $field->getHandler()->validateFormData(
                $postCustomFields ?: [],
                HandlerAbstract::CONTEXT_AGENT
            );

            foreach ($errors as $code) {
                $code = preg_replace('#^(.*?)\.#', '', $code);
                switch ($code) {
                    case 'min_length':
                        $code  = 'text_min';
                        $count = $field->getOption('agent_min_length');
                        $msg   = $trans->transChoice('user.error.form_'.$code, $count, ['count' => $count]);

                        break;
                    case 'max_length':
                        $code  = 'text_max';
                        $count = $field->getOption('agent_max_length');
                        $msg   = $trans->transChoice('user.error.form_'.$code, $count, ['count' => $count]);

                        break;
                    case 'regex_fail':
                        $code = 'text_regex';
                        $msg  = $trans->getPhraseText('user.error.form_'.$code);

                        break;
                    default:
                        $msg = $trans->getPhraseText('user.error.form_'.$code);
                }

                $fieldErrors['field_'.$field->getId()][] = $msg;
            }
        }
    }
}
